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

    protected function getAdminNavigationLinks(): array
    {
        return [
            ['key' => 'post', 'label' => 'Articles', 'url' => $this->generateUrl('everpsblog_admin_post')],
            ['key' => 'category', 'label' => 'Catégories', 'url' => $this->generateUrl('everpsblog_admin_category')],
            ['key' => 'tag', 'label' => 'Tags', 'url' => $this->generateUrl('everpsblog_admin_tag')],
            ['key' => 'author', 'label' => 'Auteurs', 'url' => $this->generateUrl('everpsblog_admin_author')],
            ['key' => 'comment', 'label' => 'Commentaires', 'url' => $this->generateUrl('everpsblog_admin_comment')],
            ['key' => 'configuration', 'label' => 'Configuration', 'url' => $this->generateUrl('everpsblog_admin_dashboard')],
        ];
    }

}
