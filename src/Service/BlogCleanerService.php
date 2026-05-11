<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}


class BlogCleanerService
{
    public function convertToArray($value)
    {
        $array = is_array($value) ? $value : [$value];

        foreach ($array as $key => $item) {
            if (\Validate::isInt($item)) {
                $array[$key] = (int) $item;
            }
        }

        return $array;
    }
}
