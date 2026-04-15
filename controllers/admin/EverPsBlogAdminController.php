<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class EverPsBlogAdminController extends ModuleAdminController
{
    protected $module_name = 'everpsblog';
    protected $shop_url;
    protected $img_url;
    protected $html;
    protected $legacyToSymfonyRoutes = [
        'AdminEverPsBlogPost' => 'everpsblog_admin_post',
        'AdminEverPsBlogCategory' => 'everpsblog_admin_category',
        'AdminEverPsBlogTag' => 'everpsblog_admin_tag',
        'AdminEverPsBlogComment' => 'everpsblog_admin_comment',
        'AdminEverPsBlogAuthor' => 'everpsblog_admin_author',
    ];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->shop_url = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $this->img_url = $this->shop_url . 'modules/' . $this->module_name . '/views/img/';
        parent::__construct();
        $this->assignCommonVars();
    }

    public function init()
    {
        $this->redirectToSymfonyRouteIfNeeded();
        parent::init();
    }

    protected function redirectToSymfonyRouteIfNeeded()
    {
        if ((bool) Tools::getValue('legacy_proxy')) {
            return;
        }

        $controllerName = Tools::getValue('controller');
        if (!$controllerName || !isset($this->legacyToSymfonyRoutes[$controllerName])) {
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

    protected function assignCommonVars()
    {
        $moduleConfUrl = 'index.php?controller=AdminModules&configure=' . $this->module_name . '&token=' . Tools::getAdminTokenLite('AdminModules');
        $postUrl = $this->context->link->getAdminLink('everpsblog_admin_post');
        $authorUrl = $this->context->link->getAdminLink('everpsblog_admin_author');
        $categoryUrl = $this->context->link->getAdminLink('everpsblog_admin_category');
        $tagUrl = $this->context->link->getAdminLink('everpsblog_admin_tag');
        $commentUrl = $this->context->link->getAdminLink('everpsblog_admin_comment');
        $blogUrl = $this->context->link->getModuleLink($this->module_name, 'blog', [], true);
        $ever_blog_token = Tools::encrypt('everpsblog/cron');
        $emptytrash = $this->context->link->getModuleLink(
            $this->module_name,
            'emptytrash',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) Context::getContext()->shop->id,
            ],
            true,
            (int) Context::getContext()->language->id,
            (int) Context::getContext()->shop->id
        );
        $pending = $this->context->link->getModuleLink(
            $this->module_name,
            'pending',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) Context::getContext()->shop->id,
            ],
            true,
            (int) Context::getContext()->language->id,
            (int) Context::getContext()->shop->id
        );
        $planned = $this->context->link->getModuleLink(
            $this->module_name,
            'planned',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) Context::getContext()->shop->id,
            ],
            true,
            (int) Context::getContext()->language->id,
            (int) Context::getContext()->shop->id
        );

        $this->context->smarty->assign([
            'image_dir' => $this->shop_url . 'modules/' . $this->module_name . '/views/img/',
            'everpsblogcron' => $emptytrash,
            'everpsblogcronpending' => $pending,
            'everpsblogcronplanned' => $planned,
            'moduleConfUrl' => $moduleConfUrl,
            'authorUrl' => $authorUrl,
            'postUrl' => $postUrl,
            'categoryUrl' => $categoryUrl,
            'tagUrl' => $tagUrl,
            'commentUrl' => $commentUrl,
            'blogUrl' => $blogUrl,
        ]);
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            'Modules.Everpsblog.' . get_class($this)
        );
    }

    protected function addToolbarNavigationButtons()
    {
        $this->page_header_toolbar_btn['module_config'] = [
            'href' => 'index.php?controller=AdminModules&configure=' . $this->module_name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Module configuration'),
            'icon' => 'process-icon-cogs',
        ];
        $this->page_header_toolbar_btn['posts'] = [
            'href' => $this->context->link->getAdminLink('everpsblog_admin_post'),
            'desc' => $this->l('Posts'),
            'icon' => 'process-icon-list',
        ];
        $this->page_header_toolbar_btn['categories'] = [
            'href' => $this->context->link->getAdminLink('everpsblog_admin_category'),
            'desc' => $this->l('Categories'),
            'icon' => 'process-icon-categories',
        ];
        $this->page_header_toolbar_btn['tags'] = [
            'href' => $this->context->link->getAdminLink('everpsblog_admin_tag'),
            'desc' => $this->l('Tags'),
            'icon' => 'process-icon-tag',
        ];
        $this->page_header_toolbar_btn['comments'] = [
            'href' => $this->context->link->getAdminLink('everpsblog_admin_comment'),
            'desc' => $this->l('Comments'),
            'icon' => 'process-icon-comments',
        ];
        $this->page_header_toolbar_btn['authors'] = [
            'href' => $this->context->link->getAdminLink('everpsblog_admin_author'),
            'desc' => $this->l('Authors'),
            'icon' => 'process-icon-user',
        ];
    }

    public function initPageHeaderToolbar()
    {
        $this->addToolbarNavigationButtons();
        $this->page_header_toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new element'),
            'icon' => 'process-icon-new',
        ];
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $lists = parent::renderList();
        $this->html = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/' . $this->module_name . '/views/templates/admin/headerController.tpl'
        );
        $blog_instance = Module::getInstanceByName($this->module_name);
        if ($blog_instance->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . '/' . $this->module_name . '/views/templates/admin/upgrade.tpl'
            );
        }
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/' . $this->module_name . '/views/templates/admin/footer.tpl'
        );
        return $this->html;
    }

    protected function processBulkDelete()
    {
        if (!$this->access('delete')) {
            $this->errors[] = $this->l('You do not have permission to delete this item.');

            return;
        }

        $idsToDelete = Tools::getValue($this->table . 'Box');
        if (!is_array($idsToDelete) || empty($idsToDelete)) {
            return;
        }

        foreach ($idsToDelete as $idEverObj) {
            $idEverObj = (int) $idEverObj;

            if ($idEverObj <= 0) {
                continue;
            }

            $everObj = new $this->className($idEverObj);
            if (!$everObj->delete()) {
                $this->errors[] = sprintf(
                    $this->l('An error has occurred: Can\'t delete the current object with ID %d'),
                    $idEverObj
                );

                continue;
            }

            $this->logSensitiveAction('bo_bulk_delete', [
                'resource' => $this->table,
                'entity_id' => $idEverObj,
            ]);
        }
    }


    protected function getCsrfToken(string $tokenId): string
    {
        try {
            $tokenManager = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance()->get('security.csrf.token_manager');

            return $tokenManager->getToken($tokenId)->getValue();
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($exception->getMessage());

            return '';
        }
    }

    protected function isValidCsrfToken(string $tokenId, string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $tokenManager = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance()->get('security.csrf.token_manager');

            return $tokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken($tokenId, $token));
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($exception->getMessage());

            return false;
        }
    }

    protected function logSensitiveAction(string $action, array $context = [])
    {
        $employeeId = (int) (Context::getContext()->employee->id ?? 0);
        $shopId = (int) (Context::getContext()->shop->id ?? 0);

        $message = sprintf(
            '[everpsblog][sensitive_action] %s %s',
            $action,
            json_encode(array_merge([
                'employee_id' => $employeeId,
                'shop_id' => $shopId,
            ], $context))
        );

        PrestaShopLogger::addLog($message, 1, null, 'EverPsBlog', 0, true);
    }

    protected function displayError($message, $description = false)
    {
        array_push($this->errors, $this->module->l($message), $description);
        return $this->setTemplate('error.tpl');
    }
}
