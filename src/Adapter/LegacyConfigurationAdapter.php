<?php

namespace PrestaShop\Module\Everpsblog\Adapter;

use Configuration;

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
