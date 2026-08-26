<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Adapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class LegacyToolsAdapter
{
    public static function encrypt(string $value): string
    {
        if (\class_exists('\\Tools') && \method_exists('\\Tools', 'encrypt')) {
            return (string) \Tools::encrypt($value);
        }

        if (\class_exists('\\Tools') && \method_exists('\\Tools', 'hash')) {
            return (string) \Tools::hash($value);
        }

        if (\defined('_COOKIE_KEY_')) {
            return md5(_COOKIE_KEY_ . $value);
        }

        return md5($value);
    }
}
