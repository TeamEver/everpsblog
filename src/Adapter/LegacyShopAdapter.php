<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Adapter;

use Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}


class LegacyShopAdapter
{
    public function isFeatureActive(): bool
    {
        return (bool) Shop::isFeatureActive();
    }

    public function isShopContext(int $context): bool
    {
        return Shop::getContext() === $context;
    }

    public function getContextShopId(): int
    {
        return (int) Shop::getContextShopID();
    }
}
