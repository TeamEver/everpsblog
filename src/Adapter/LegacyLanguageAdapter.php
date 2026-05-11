<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Adapter;

use Configuration;
use Language;

if (!defined('_PS_VERSION_')) {
    exit;
}


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
