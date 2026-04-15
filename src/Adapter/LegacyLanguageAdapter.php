<?php

namespace PrestaShop\Module\Everpsblog\Adapter;

use Configuration;
use Language;

class LegacyLanguageAdapter
{
    public function getLanguages(bool $activeOnly = false): array
    {
        return (array) Language::getLanguages($activeOnly);
    }

    public function getDefaultLanguageId(): int
    {
        return (int) Configuration::get('PS_LANG_DEFAULT');
    }

    public function createLanguage(int $languageId): Language
    {
        return new Language($languageId);
    }
}
