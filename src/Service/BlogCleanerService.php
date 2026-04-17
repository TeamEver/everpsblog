<?php

namespace PrestaShop\Module\Everpsblog\Service;

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
