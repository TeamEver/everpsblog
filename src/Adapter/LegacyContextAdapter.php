<?php

namespace PrestaShop\Module\Everpsblog\Adapter;

use Context;

class LegacyContextAdapter
{
    public function getContext(): Context
    {
        return Context::getContext();
    }
}
