<?php

namespace PrestaShop\Module\Everpsblog\Service;

class ModuleTranslationCatalogService
{
    private const MODULE_NAME = 'everpsblog';
    private const EXPORT_FORMAT = 'everpsblog-translations-v1';

    /**
     * @param string[] $domains
     *
     * @return array<string, array{translation:string,theme:?string}>
     */
    public function getTranslationsIndex(int $idLang, array $domains = []): array
    {
        if ($idLang <= 0 || !$this->translationTableExists()) {
            return [];
        }

        $where = 't.`id_lang` = ' . (int) $idLang;
        $normalizedDomains = array_values(array_filter(array_map('strval', $domains)));
        if (!empty($normalizedDomains)) {
            $escapedDomains = array_map(function (string $domain): string {
                return '"' . pSQL($domain) . '"';
            }, $normalizedDomains);
            $where .= ' AND t.`domain` IN (' . implode(', ', $escapedDomains) . ')';
        } else {
            $where .= ' AND LOWER(REPLACE(t.`domain`, ".", "")) LIKE "moduleseverpsblog%"';
        }

        $rows = \Db::getInstance()->executeS(
            'SELECT t.`domain`, t.`key`, t.`translation`, t.`theme`
             FROM `' . _DB_PREFIX_ . 'translation` t
             WHERE ' . $where
        ) ?: [];

        $translations = [];
        foreach ($rows as $row) {
            $domain = (string) ($row['domain'] ?? '');
            $key = (string) ($row['key'] ?? '');
            if ('' === $domain || '' === $key || !$this->isModuleDomain($domain)) {
                continue;
            }

            $translations[$domain . "\0" . $key] = [
                'translation' => (string) ($row['translation'] ?? ''),
                'theme' => null === ($row['theme'] ?? null) || '' === (string) ($row['theme'] ?? '')
                    ? null
                    : (string) $row['theme'],
            ];
        }

        return $translations;
    }

    public function saveTranslation(int $idLang, string $domain, string $key, string $translation, ?string $theme = null): void
    {
        if (!$this->translationTableExists()) {
            throw new \RuntimeException('The PrestaShop translation table is not available.');
        }

        if ($idLang <= 0 || '' === trim($domain) || '' === trim($key) || !$this->isModuleDomain($domain)) {
            throw new \InvalidArgumentException('Invalid module translation item.');
        }

        $this->upsertTranslation($idLang, $domain, $key, $translation, $theme);
    }

    public function export(): array
    {
        if (!$this->translationTableExists()) {
            return $this->buildExportPayload([]);
        }

        $rows = \Db::getInstance()->executeS(
            'SELECT t.`id_lang`, t.`domain`, t.`key`, t.`translation`, t.`theme`, l.`iso_code`, l.`locale`
             FROM `' . _DB_PREFIX_ . 'translation` t
             LEFT JOIN `' . _DB_PREFIX_ . 'lang` l ON l.`id_lang` = t.`id_lang`
             WHERE LOWER(REPLACE(t.`domain`, ".", "")) LIKE "moduleseverpsblog%"
             ORDER BY l.`iso_code` ASC, t.`domain` ASC, t.`key` ASC'
        ) ?: [];

        $translations = [];
        foreach ($rows as $row) {
            $isoCode = (string) ($row['iso_code'] ?? '');
            if ('' === $isoCode || !$this->isModuleDomain((string) ($row['domain'] ?? ''))) {
                continue;
            }

            if (!isset($translations[$isoCode])) {
                $translations[$isoCode] = [
                    'locale' => (string) ($row['locale'] ?? ''),
                    'items' => [],
                ];
            }

            $translations[$isoCode]['items'][] = [
                'domain' => (string) $row['domain'],
                'key' => (string) $row['key'],
                'translation' => (string) $row['translation'],
                'theme' => null === ($row['theme'] ?? null) || '' === (string) ($row['theme'] ?? '')
                    ? null
                    : (string) $row['theme'],
            ];
        }

        return $this->buildExportPayload($translations);
    }

    public function importFromJson(string $json): array
    {
        if (!$this->translationTableExists()) {
            throw new \RuntimeException('The PrestaShop translation table is not available.');
        }

        $payload = json_decode($json, true);
        if (!is_array($payload) || ($payload['format'] ?? '') !== self::EXPORT_FORMAT) {
            throw new \InvalidArgumentException('Invalid Ever Blog translation export file.');
        }

        $translations = $payload['translations'] ?? [];
        if (!is_array($translations)) {
            throw new \InvalidArgumentException('The translation export does not contain any translation.');
        }

        $languageIdsByIsoCode = $this->getLanguageIdsByIsoCode();
        $stats = [
            'imported' => 0,
            'skipped' => 0,
        ];

        foreach ($translations as $isoCode => $languagePayload) {
            $isoCode = (string) $isoCode;
            $idLang = (int) ($languageIdsByIsoCode[$isoCode] ?? 0);
            $items = is_array($languagePayload) ? ($languagePayload['items'] ?? []) : [];
            if ($idLang <= 0 || !is_array($items)) {
                $stats['skipped'] += is_array($items) ? count($items) : 0;
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    ++$stats['skipped'];
                    continue;
                }

                $domain = trim((string) ($item['domain'] ?? ''));
                $key = trim((string) ($item['key'] ?? ''));
                $translation = (string) ($item['translation'] ?? '');
                $theme = $this->normalizeTheme($item['theme'] ?? null);

                if ('' === $domain || '' === $key || !$this->isModuleDomain($domain)) {
                    ++$stats['skipped'];
                    continue;
                }

                $this->upsertTranslation($idLang, $domain, $key, $translation, $theme);
                ++$stats['imported'];
            }
        }

        return $stats;
    }

    public function importFromFile(string $filePath): array
    {
        if (!is_file($filePath)) {
            return [
                'imported' => 0,
                'skipped' => 0,
            ];
        }

        $content = file_get_contents($filePath);
        if (false === $content || '' === trim($content)) {
            return [
                'imported' => 0,
                'skipped' => 0,
            ];
        }

        return $this->importFromJson((string) $content);
    }

    public function importLanguageFromFile(string $filePath, int $idLang): array
    {
        if ($idLang <= 0 || !is_file($filePath)) {
            return [
                'imported' => 0,
                'skipped' => 0,
            ];
        }

        $content = file_get_contents($filePath);
        if (false === $content || '' === trim($content)) {
            return [
                'imported' => 0,
                'skipped' => 0,
            ];
        }

        return $this->importLanguageFromJson((string) $content, $idLang);
    }

    public function deleteModuleTranslations(): int
    {
        if (!$this->translationTableExists()) {
            return 0;
        }

        \Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'translation`
             WHERE LOWER(REPLACE(`domain`, ".", "")) LIKE "moduleseverpsblog%"'
        );

        return (int) \Db::getInstance()->Affected_Rows();
    }

    public function importLanguageFromJson(string $json, int $idLang): array
    {
        if (!$this->translationTableExists()) {
            throw new \RuntimeException('The PrestaShop translation table is not available.');
        }

        if ($idLang <= 0) {
            throw new \InvalidArgumentException('Invalid language identifier.');
        }

        $payload = json_decode($json, true);
        if (!is_array($payload) || ($payload['format'] ?? '') !== self::EXPORT_FORMAT) {
            throw new \InvalidArgumentException('Invalid Ever Blog translation export file.');
        }

        $targetLanguage = new \Language($idLang);
        if (!\Validate::isLoadedObject($targetLanguage)) {
            throw new \InvalidArgumentException('The target language does not exist.');
        }

        $targetIsoCode = (string) ($targetLanguage->iso_code ?? '');
        $translations = $payload['translations'] ?? [];
        $languagePayload = is_array($translations) ? ($translations[$targetIsoCode] ?? null) : null;
        $items = is_array($languagePayload) ? ($languagePayload['items'] ?? []) : [];

        $stats = [
            'imported' => 0,
            'skipped' => 0,
        ];

        if (!is_array($items)) {
            return $stats;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                ++$stats['skipped'];
                continue;
            }

            $domain = trim((string) ($item['domain'] ?? ''));
            $key = trim((string) ($item['key'] ?? ''));
            $translation = (string) ($item['translation'] ?? '');
            $theme = $this->normalizeTheme($item['theme'] ?? null);

            if ('' === $domain || '' === $key || !$this->isModuleDomain($domain)) {
                ++$stats['skipped'];
                continue;
            }

            $this->upsertTranslation($idLang, $domain, $key, $translation, $theme);
            ++$stats['imported'];
        }

        return $stats;
    }

    private function buildExportPayload(array $translations): array
    {
        return [
            'format' => self::EXPORT_FORMAT,
            'module' => self::MODULE_NAME,
            'exported_at' => date(DATE_ATOM),
            'translations' => $translations,
        ];
    }

    private function translationTableExists(): bool
    {
        return (bool) \Db::getInstance()->executeS(
            'SHOW TABLES LIKE "' . pSQL(_DB_PREFIX_ . 'translation') . '"'
        );
    }

    private function isModuleDomain(string $domain): bool
    {
        $normalizedDomain = strtolower(str_replace('.', '', $domain));

        return 0 === strpos($normalizedDomain, 'moduleseverpsblog');
    }

    private function getLanguageIdsByIsoCode(): array
    {
        $idsByIsoCode = [];
        foreach (\Language::getLanguages(false) as $language) {
            $isoCode = (string) ($language['iso_code'] ?? '');
            $idLang = (int) ($language['id_lang'] ?? 0);
            if ('' !== $isoCode && $idLang > 0) {
                $idsByIsoCode[$isoCode] = $idLang;
            }
        }

        return $idsByIsoCode;
    }

    private function upsertTranslation(int $idLang, string $domain, string $key, string $translation, ?string $theme): void
    {
        $where = '`id_lang` = ' . (int) $idLang
            . ' AND `domain` = "' . pSQL($domain) . '"'
            . ' AND `key` = "' . pSQL($key, true) . '"';

        if (null === $theme) {
            $where .= ' AND (`theme` IS NULL OR `theme` = "")';
        } else {
            $where .= ' AND `theme` = "' . pSQL($theme) . '"';
        }

        $exists = (bool) \Db::getInstance()->getValue(
            'SELECT 1 FROM `' . _DB_PREFIX_ . 'translation` WHERE ' . $where
        );

        if ($exists) {
            \Db::getInstance()->update('translation', [
                'translation' => pSQL($translation, true),
            ], $where);

            return;
        }

        $themeValue = null === $theme ? 'NULL' : '"' . pSQL($theme) . '"';
        \Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'translation` (`id_lang`, `domain`, `key`, `translation`, `theme`)
             VALUES (
                ' . (int) $idLang . ',
                "' . pSQL($domain) . '",
                "' . pSQL($key, true) . '",
                "' . pSQL($translation, true) . '",
                ' . $themeValue . '
             )'
        );
    }

    /**
     * @param mixed $theme
     */
    private function normalizeTheme($theme): ?string
    {
        $theme = is_string($theme) ? trim($theme) : '';

        return '' !== $theme ? $theme : null;
    }
}
