<?php

namespace PrestaShop\Module\Everpsblog\Service;

use Context;
use PrestaShop\Module\Everpsblog\Adapter\LegacyContextAdapter;

class ContextStateService
{
    /** @var LegacyContextAdapter */
    private $contextAdapter;

    public function __construct(LegacyContextAdapter $contextAdapter)
    {
        $this->contextAdapter = $contextAdapter;
    }

    public function getContext(): Context
    {
        return $this->contextAdapter->getContext();
    }

    public function getShopId(): int
    {
        return (int) $this->getContext()->shop->id;
    }

    public function getLanguageId(): int
    {
        return (int) $this->getContext()->language->id;
    }
}
