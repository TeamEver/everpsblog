<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class EverPsBlogAdminController extends ModuleAdminController
{
    /**
     * Correspondance stricte des contrôleurs legacy vers les routes Symfony BO.
     */
    private $legacyToSymfonyRoutes = [
        'AdminEverPsBlogPost' => 'everpsblog_admin_post',
        'AdminEverPsBlogCategory' => 'everpsblog_admin_category',
        'AdminEverPsBlogTag' => 'everpsblog_admin_tag',
        'AdminEverPsBlogComment' => 'everpsblog_admin_comment',
        'AdminEverPsBlogAuthor' => 'everpsblog_admin_author',
    ];

    public function init()
    {
        $this->redirectToSymfonyRoute();
        parent::init();
    }

    private function redirectToSymfonyRoute(): void
    {
        PrestaShopLogger::addLog(
            sprintf('[everpsblog][deprecated] Legacy BO controller access detected: %s', static::class),
            1,
            null,
            'EverPsBlog'
        );
        $controllerName = (string) Tools::getValue('controller');
        if ($controllerName === '' || !isset($this->legacyToSymfonyRoutes[$controllerName])) {
            return;
        }

        $queryParameters = $_GET;
        unset($queryParameters['controller'], $queryParameters['token']);
        $queryParameters['legacy_redirect'] = 1;

        $url = $this->context->link->getAdminLink(
            $this->legacyToSymfonyRoutes[$controllerName],
            true,
            [],
            $queryParameters
        );

        Tools::redirectAdmin($url);
    }
}
