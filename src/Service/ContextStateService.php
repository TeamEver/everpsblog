<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

use Context;
use PrestaShop\Module\Everpsblog\Adapter\LegacyContextAdapter;

if (!defined('_PS_VERSION_')) {
    exit;
}


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
