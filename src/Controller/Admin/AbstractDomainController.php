<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use Context;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

abstract class AbstractDomainController extends FrameworkBundleAdminController
{
    protected function getContextShopId(): int
    {
        return (int) Context::getContext()->shop->id;
    }

    protected function getContextLangId(): int
    {
        return (int) Context::getContext()->language->id;
    }
}
