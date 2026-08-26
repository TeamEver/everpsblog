<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Adapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class LegacyProductListingPresenterAdapter
{
    public static function create(\Context $context)
    {
        $presenterClass = self::resolvePresenterClass();

        return new $presenterClass(
            new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($context->link),
            $context->link,
            new \PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
            new \PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
            $context->getTranslator()
        );
    }

    private static function resolvePresenterClass(): string
    {
        $classes = [
            '\\PrestaShop\\PrestaShop\\Core\\Product\\ProductListingPresenter',
            '\\PrestaShop\\PrestaShop\\Adapter\\Presenter\\Product\\ProductListingPresenter',
        ];

        foreach ($classes as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \RuntimeException('Product listing presenter is not available.');
    }
}
