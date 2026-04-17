<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

abstract class AbstractDomainController extends FrameworkBundleAdminController
{
    /** @var ContextStateService */
    protected $contextStateService;

    public function __construct(ContextStateService $contextStateService)
    {
        $this->contextStateService = $contextStateService;
    }

    protected function getContextShopId(): int
    {
        return $this->contextStateService->getShopId();
    }

    protected function getContextLangId(): int
    {
        return $this->contextStateService->getLanguageId();
    }

}
