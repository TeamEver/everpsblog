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

    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->shop_url = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $this->img_url = $this->shop_url . 'modules/' . $this->module_name . '/views/img/';
        parent::__construct();
        $this->assignCommonVars();
    }

    protected function assignCommonVars()
    {
        $moduleConfUrl = 'index.php?controller=AdminModules&configure=' . $this->module_name . '&token=' . Tools::getAdminTokenLite('AdminModules');
        $postUrl = 'index.php?controller=AdminEverPsBlogPost&token=' . Tools::getAdminTokenLite('AdminEverPsBlogPost');
        $authorUrl = 'index.php?controller=AdminEverPsBlogAuthor&token=' . Tools::getAdminTokenLite('AdminEverPsBlogAuthor');
        $categoryUrl = 'index.php?controller=AdminEverPsBlogCategory&token=' . Tools::getAdminTokenLite('AdminEverPsBlogCategory');
        $tagUrl = 'index.php?controller=AdminEverPsBlogTag&token=' . Tools::getAdminTokenLite('AdminEverPsBlogTag');
        $commentUrl = 'index.php?controller=AdminEverPsBlogComment&token=' . Tools::getAdminTokenLite('AdminEverPsBlogComment');
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
            'href' => 'index.php?controller=AdminEverPsBlogPost&token=' . Tools::getAdminTokenLite('AdminEverPsBlogPost'),
            'desc' => $this->l('Posts'),
            'icon' => 'process-icon-list',
        ];
        $this->page_header_toolbar_btn['categories'] = [
            'href' => 'index.php?controller=AdminEverPsBlogCategory&token=' . Tools::getAdminTokenLite('AdminEverPsBlogCategory'),
            'desc' => $this->l('Categories'),
            'icon' => 'process-icon-categories',
        ];
        $this->page_header_toolbar_btn['tags'] = [
            'href' => 'index.php?controller=AdminEverPsBlogTag&token=' . Tools::getAdminTokenLite('AdminEverPsBlogTag'),
            'desc' => $this->l('Tags'),
            'icon' => 'process-icon-tag',
        ];
        $this->page_header_toolbar_btn['comments'] = [
            'href' => 'index.php?controller=AdminEverPsBlogComment&token=' . Tools::getAdminTokenLite('AdminEverPsBlogComment'),
            'desc' => $this->l('Comments'),
            'icon' => 'process-icon-comments',
        ];
        $this->page_header_toolbar_btn['authors'] = [
            'href' => 'index.php?controller=AdminEverPsBlogAuthor&token=' . Tools::getAdminTokenLite('AdminEverPsBlogAuthor'),
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
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new $this->className((int) $idEverObj);
            if (!$everObj->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function displayError($message, $description = false)
    {
        array_push($this->errors, $this->module->l($message), $description);
        return $this->setTemplate('error.tpl');
    }
}
