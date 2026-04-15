<?php

namespace PrestaShop\Module\Everpsblog\Adapter;

use Shop;

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
