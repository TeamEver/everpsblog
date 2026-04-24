<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class ModuleAutoTranslationService
{
    /** @var ModuleTranslationSourceExtractor */
    private $sourceExtractor;

    /** @var GoogleTranslateFreeClient */
    private $googleTranslateClient;

    /** @var ModuleTranslationCatalogService */
    private $translationCatalogService;

    public function __construct(
        ModuleTranslationSourceExtractor $sourceExtractor,
        GoogleTranslateFreeClient $googleTranslateClient,
        ModuleTranslationCatalogService $translationCatalogService
    ) {
        $this->sourceExtractor = $sourceExtractor;
        $this->googleTranslateClient = $googleTranslateClient;
        $this->translationCatalogService = $translationCatalogService;
    }

    /**
     * @return array{detected:int,queued:int,translated:int,saved:int,skipped_existing:int,skipped_unchanged:int}
     */
    public function translateModule(int $sourceLangId, int $targetLangId, string $scope, bool $overwriteExisting = false): array
    {
        $sourceLanguage = $this->getLanguageById($sourceLangId);
        $targetLanguage = $this->getLanguageById($targetLangId);

        if (null === $sourceLanguage || null === $targetLanguage) {
            throw new \InvalidArgumentException('Please select valid installed languages.');
        }

        if ($sourceLangId === $targetLangId) {
            throw new \InvalidArgumentException('Source and destination languages must be different.');
        }

        if (!in_array($scope, ['admin', 'shop', 'all'], true)) {
            throw new \InvalidArgumentException('Invalid translation scope.');
        }

        $entries = $this->sourceExtractor->extract($scope);
        $stats = [
            'detected' => count($entries),
            'queued' => 0,
            'translated' => 0,
            'saved' => 0,
            'skipped_existing' => 0,
            'skipped_unchanged' => 0,
        ];

        if (empty($entries)) {
            return $stats;
        }

        $targetTranslations = $this->translationCatalogService->getTranslationsIndex(
            $targetLangId,
            $this->resolveDomains($scope)
        );
        $translator = \Context::getContext()->getTranslator();
        $sourceLocale = $this->resolveLocale($sourceLanguage);
        $sourceCode = $this->resolveGoogleLanguageCode($sourceLanguage);
        $targetCode = $this->resolveGoogleLanguageCode($targetLanguage);
        $translationQueue = [];
        $textsToTranslate = [];

        foreach ($entries as $entry) {
            $identifier = $entry['domain'] . "\0" . $entry['key'];
            $existingTranslation = $targetTranslations[$identifier]['translation'] ?? '';
            if (!$overwriteExisting && '' !== trim((string) $existingTranslation)) {
                ++$stats['skipped_existing'];
                continue;
            }

            $sourceText = (string) $translator->trans($entry['key'], [], $entry['domain'], $sourceLocale);
            $sourceText = '' !== trim($sourceText) ? $sourceText : $entry['key'];

            $translationQueue[] = [
                'domain' => $entry['domain'],
                'key' => $entry['key'],
                'source_text' => $sourceText,
                'theme' => $targetTranslations[$identifier]['theme'] ?? null,
                'existing_translation' => (string) $existingTranslation,
            ];
            $textsToTranslate[$sourceText] = $sourceText;
            ++$stats['queued'];
        }

        if (empty($translationQueue)) {
            return $stats;
        }

        $translatedTexts = $this->googleTranslateClient->translateTexts(array_values($textsToTranslate), $sourceCode, $targetCode);
        $stats['translated'] = count($translatedTexts);

        foreach ($translationQueue as $translationItem) {
            $translatedText = (string) ($translatedTexts[$translationItem['source_text']] ?? '');
            $translatedText = '' !== trim($translatedText) ? $translatedText : $translationItem['source_text'];

            if ($translatedText === (string) $translationItem['existing_translation']) {
                ++$stats['skipped_unchanged'];
                continue;
            }

            $this->translationCatalogService->saveTranslation(
                $targetLangId,
                (string) $translationItem['domain'],
                (string) $translationItem['key'],
                $translatedText,
                isset($translationItem['theme']) && null !== $translationItem['theme']
                    ? (string) $translationItem['theme']
                    : null
            );
            ++$stats['saved'];
        }

        $this->clearCaches();

        return $stats;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function getLanguageById(int $langId): ?array
    {
        foreach (\Language::getLanguages(false) as $language) {
            if ((int) ($language['id_lang'] ?? 0) === $langId) {
                return $language;
            }
        }

        return null;
    }

    private function resolveLocale(array $language): string
    {
        $locale = trim((string) ($language['locale'] ?? ''));
        if ('' !== $locale) {
            return $locale;
        }

        $isoCode = trim((string) ($language['iso_code'] ?? ''));

        return '' !== $isoCode ? $isoCode : 'en';
    }

    private function resolveGoogleLanguageCode(array $language): string
    {
        $locale = strtolower(str_replace('_', '-', trim((string) ($language['locale'] ?? ''))));
        $isoCode = strtolower(trim((string) ($language['iso_code'] ?? '')));
        $specialMap = [
            'br' => 'pt',
            'pt-br' => 'pt',
            'pt-pt' => 'pt',
            'zh-cn' => 'zh-CN',
            'zh-tw' => 'zh-TW',
            'zh-hk' => 'zh-TW',
        ];

        if (isset($specialMap[$locale])) {
            return $specialMap[$locale];
        }

        if (isset($specialMap[$isoCode])) {
            return $specialMap[$isoCode];
        }

        if ('' !== $isoCode) {
            return $isoCode;
        }

        if (false !== strpos($locale, '-')) {
            $primary = strtok($locale, '-');

            return false !== $primary && '' !== $primary ? $primary : 'en';
        }

        return '' !== $locale ? $locale : 'en';
    }

    /**
     * @return string[]
     */
    private function resolveDomains(string $scope): array
    {
        if ('admin' === $scope) {
            return [ModuleTranslationSourceExtractor::ADMIN_DOMAIN];
        }

        if ('shop' === $scope) {
            return [ModuleTranslationSourceExtractor::SHOP_DOMAIN];
        }

        return [
            ModuleTranslationSourceExtractor::ADMIN_DOMAIN,
            ModuleTranslationSourceExtractor::SHOP_DOMAIN,
        ];
    }

    private function clearCaches(): void
    {
        try {
            if (class_exists('\Tools') && method_exists('\Tools', 'clearCache')) {
                \Tools::clearCache();
            }

            if (class_exists('\Tools') && method_exists('\Tools', 'clearSmartyCache')) {
                \Tools::clearSmartyCache();
            }
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('[everpsblog][ModuleAutoTranslationService] ' . $exception->getMessage(), 2);
        }
    }
}
