<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Adapter;

use Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}


class LegacyConfigurationAdapter
{
    public function get(string $key, $default = null, ?int $shopId = null)
    {
        $value = Configuration::get($key, null, null, $shopId);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}
