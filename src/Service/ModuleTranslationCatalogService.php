<?php

namespace PrestaShop\Module\Everpsblog\Service;

class ModuleTranslationCatalogService
{
    private const MODULE_NAME = 'everpsblog';
    private const EXPORT_FORMAT = 'everpsblog-translations-v1';

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
                'theme' => (string) ($row['theme'] ?? ''),
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
                $theme = trim((string) ($item['theme'] ?? ''));

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

    private function upsertTranslation(int $idLang, string $domain, string $key, string $translation, string $theme): void
    {
        $where = '`id_lang` = ' . (int) $idLang
            . ' AND `domain` = "' . pSQL($domain) . '"'
            . ' AND `key` = "' . pSQL($key) . '"'
            . ' AND `theme` = "' . pSQL($theme) . '"';

        $data = [
            'id_lang' => $idLang,
            'domain' => pSQL($domain),
            'key' => pSQL($key),
            'translation' => pSQL($translation, true),
            'theme' => pSQL($theme),
        ];

        $exists = (bool) \Db::getInstance()->getValue(
            'SELECT 1 FROM `' . _DB_PREFIX_ . 'translation` WHERE ' . $where
        );

        if ($exists) {
            \Db::getInstance()->update('translation', [
                'translation' => pSQL($translation, true),
            ], $where);

            return;
        }

        \Db::getInstance()->insert('translation', $data);
    }
}
