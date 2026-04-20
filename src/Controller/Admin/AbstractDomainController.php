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

    /**
     * Build a debug-friendly description of a caught exception.
     * Always safe to display in the BO; includes class + message + root cause
     * so the operator can act on the error without grep-ing the PS logs.
     */
    protected function describeException(\Throwable $exception): string
    {
        $parts = [];
        $current = $exception;
        while (null !== $current) {
            $parts[] = sprintf(
                '%s: %s (@%s:%d)',
                (new \ReflectionClass($current))->getShortName(),
                $current->getMessage(),
                basename($current->getFile()),
                $current->getLine()
            );
            $current = $current->getPrevious();
        }

        return implode(' <= ', $parts);
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
