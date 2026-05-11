<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Adapter;

use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}


class LegacyContextAdapter
{
    public function getContext(): Context
    {
        return Context::getContext();
    }
}
