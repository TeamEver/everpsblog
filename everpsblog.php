<?php
/**
 * 2019-2021 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogAuthor.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogImage.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTaxonomy.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogSitemap.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCleaner.php';
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlog extends Module
{
    private $html;
    private $postErrors = array();
    private $postSuccess = array();
    public static $route = array();

    public function __construct()
    {
        $this->name = 'everpsblog';
        $this->tab = 'front_office_features';
        $this->version = '5.3.20';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->siteUrl = Tools::getHttpHost(true).__PS_BASE_URI__;
        $this->module_folder = _PS_MODULE_DIR_.'everpsblog';
        parent::__construct();

        $this->displayName = $this->l('Ever Blog');
        $this->description = $this->l('Simply a blog 😀');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module ?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->context = Context::getContext();
    }

    public function install()
    {
        // Install SQL
        include(dirname(__FILE__).'/install/install.php');
        // Create hooks
        include(dirname(__FILE__).'/install/hooks-install.php');
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_.'post')) {
            mkdir(_PS_IMG_DIR_.'post', 0755, true);
        }
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_.'category')) {
            mkdir(_PS_IMG_DIR_.'category', 0755, true);
        }
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_.'tag')) {
            mkdir(_PS_IMG_DIR_.'tag', 0755, true);
        }
        // Creating img folders
        if (!file_exists(_PS_IMG_DIR_.'author')) {
            mkdir(_PS_IMG_DIR_.'author', 0755, true);
        }
        // Creating root category
        $shops = Shop::getShops();
        foreach ($shops as $shop) {
            $root_category = new EverPsBlogCategory();
            $root_category->is_root_category = 1;
            $root_category->active = 1;
            $root_category->id_shop = (int)$shop['id_shop'];
            foreach (Language::getLanguages(false) as $language) {
                $root_category->title[$language['id_lang']] = 'Root';
                $root_category->content[$language['id_lang']] = 'Root';
                $root_category->link_rewrite[$language['id_lang']] = 'root';
            }
            $root_category->save();
        }
        // Install
        return parent::install()
            && $this->registerBlogHook()
            && $this->registerHook('actionFrontControllerAfterInit')
            && $this->registerHook('header')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('displayRightColumn')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('overrideLayoutTemplate')
            && $this->registerHook('backofficeHeader')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('actionAdminMetaAfterWriteRobotsFile')
            && $this->registerHook('displayAdminAfterHeader')
            && $this->installModuleTab(
                'AdminEverPsBlog',
                'IMPROVE',
                $this->l('Blog')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogPost',
                'AdminEverPsBlog',
                $this->l('Posts')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogCategory',
                'AdminEverPsBlog',
                $this->l('Categories')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogTag',
                'AdminEverPsBlog',
                $this->l('Tags')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogComment',
                'AdminEverPsBlog',
                $this->l('Comments')
            )
            && $this->installModuleTab(
                'AdminEverPsBlogAuthor',
                'AdminEverPsBlog',
                $this->l('Authors')
            )
            && Configuration::updateValue('EVERPSBLOG_ROUTE', 'blog')
            && Configuration::updateValue('EVERBLOG_ADMIN_EMAIL', 1)
            && Configuration::updateValue('EVERBLOG_EMPTY_TRASH', 7)
            && Configuration::updateValue('EVERBLOG_ALLOW_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_CHECK_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_BANNED_USERS', '')
            && Configuration::updateValue('EVERBLOG_BANNED_IP', '')
            && Configuration::updateValue('EVERPSBLOG_PAGINATION', '10')
            && Configuration::updateValue('EVERPSBLOG_HOME_NBR', '4')
            && Configuration::updateValue('EVERPSBLOG_PRODUCT_NBR', '4')
            && Configuration::updateValue('EVERPSBLOG_EXCERPT', '150')
            && Configuration::updateValue('EVERPSBLOG_TITLE_LENGTH', '150')
            && Configuration::updateValue('EVERPSBLOG_BLOG_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_POST_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_CAT_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_AUTHOR_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERPSBLOG_TAG_LAYOUT', 'layouts/layout-right-column.tpl')
            && Configuration::updateValue('EVERBLOG_SITEMAP_NUMBER', 5000);
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/install/uninstall.php');
        include(dirname(__FILE__).'/install/hooks-uninstall.php');
        include(dirname(__FILE__).'/install/images-uninstall.php');

        Db::getInstance()->delete(
            'hook_module',
            'id_module = '.(int)$this->id
        );
        
        return parent::uninstall()
            && $this->uninstallModuleTab('AdminEverPsBlog')
            && $this->uninstallModuleTab('AdminEverPsBlogPost')
            && $this->uninstallModuleTab('AdminEverPsBlogCategory')
            && $this->uninstallModuleTab('AdminEverPsBlogTag')
            && $this->uninstallModuleTab('AdminEverPsBlogComment')
            && $this->uninstallModuleTab('AdminEverPsBlogAuthor');
    }

    private function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminEverPsBlog' && $this->isSeven) {
            $tab->icon = 'icon-team-ever';
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = $tabName;
        }

        return $tab->add();
    }

    private function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int)Tab::getIdFromClassName($tabClass));

        return $tab->delete();
    }

    private function registerBlogHook()
    {
        return $this->registerHook('actionBeforeEverPostInitContent')
            && $this->registerHook('actionBeforeEverCategoryInitContent')
            && $this->registerHook('actionBeforeEverTagInitContent')
            && $this->registerHook('actionBeforeEverBlogInitContent')
            && $this->registerHook('actionBeforeEverBlogInit')
            && $this->registerHook('displayBeforeEverPost')
            && $this->registerHook('displayAfterEverPost')
            && $this->registerHook('displayBeforeEverCategory')
            && $this->registerHook('displayAfterEverCategory')
            && $this->registerHook('displayBeforeEverTag')
            && $this->registerHook('displayAfterEverTag')
            && $this->registerHook('displayBeforeEverComment')
            && $this->registerHook('displayAfterEverComment')
            && $this->registerHook('displayBeforeEverLoop')
            && $this->registerHook('displayAfterEverLoop')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('actionObjectAuthorDeleteAfter')
            && $this->registerHook('actionObjectEverPsBlogTagDeleteAfter')
            && $this->registerHook('actionObjectEverPsBlogCategoryDeleteAfter')
            && $this->registerHook('actionObjectEverPsBlogPostDeleteAfter')
            && $this->registerHook('actionObjectEverPsBlogCommentDeleteAfter')
            && $this->registerHook('actionObjectProductUpdateAfter')
            && $this->registerHook('actionObjectEverPsBlogAuthorUpdateAfter')
            && $this->registerHook('actionObjectEverPsBlogTagUpdateAfter')
            && $this->registerHook('actionObjectEverPsBlogCategoryUpdateAfter')
            && $this->registerHook('actionObjectEverPsBlogPostUpdateAfter')
            && $this->registerHook('actionObjectEverPsBlogCommentUpdateAfter')
            && $this->registerHook('actionObjectProductAddAfter')
            && $this->registerHook('actionObjectAuthorAddAfter')
            && $this->registerHook('actionObjectEverPsBlogTagAddAfter')
            && $this->registerHook('actionObjectEverPsBlogCategoryAddAfter')
            && $this->registerHook('actionObjectEverPsBlogPostAddAfter')
            && $this->registerHook('actionObjectEverPsBlogCommentAddAfter')
            && $this->registerHook('actionObjectShopAddAfter')
            && $this->registerHook('actionObjectShopDeleteAfter');
    }

    /**
     * Add link rewrite rule
     * @see https://stackoverflow.com/questions/49430883/creating-a-url-rewrite-module-in-prestashop
     */
    public function hookModuleRoutes($params)
    {
        $base_route = Configuration::get('EVERPSBLOG_ROUTE') ? Configuration::get('EVERPSBLOG_ROUTE') : 'blog';

        return array(
            'module-everpsblog-blog' => array(
                'controller' => 'blog',
                'rule' => $base_route,
                'keywords' => array(
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'blog',
                )
            ),
            'module-everpsblog-category' => array(
                'controller' => 'category',
                'rule' => $base_route.'/category{/:id_ever_category}-{:link_rewrite}',
                'keywords' => array(
                    'id_ever_category' => array('regexp' => '[0-9]+', 'param' => 'id_ever_category'),
                    'link_rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                )
            ),
            'module-everpsblog-post' => array(
                'controller' => 'post',
                'rule' => $base_route.'/post{/:id_ever_post}-{:link_rewrite}',
                'keywords' => array(
                    'id_ever_post' => array('regexp' => '[0-9]+', 'param' => 'id_ever_post'),
                    'link_rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                )
            ),
            'module-everpsblog-tag' => array(
                'controller' => 'tag',
                'rule' => $base_route.'/tag{/:id_ever_tag}-{:link_rewrite}',
                'keywords' => array(
                    'id_ever_tag' => array('regexp' => '[0-9]+', 'param' => 'id_ever_tag'),
                    'link_rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                )
            ),
            'module-everpsblog-author' => array(
                'controller' => 'author',
                'rule' => $base_route.'/author{/:id_ever_author}-{:link_rewrite}',
                'keywords' => array(
                    'id_ever_author' => array('regexp' => '[0-9]+', 'param' => 'id_ever_author'),
                    'link_rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                )
            )
        );
    }

    public function getContent()
    {
        $this->checkHooks();
        $this->html = '';
        // Process internal linking
        if (Tools::isSubmit('submitGenerateBlogSitemap')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->generateBlogSitemap();
            }
        }

        if (Tools::isSubmit('submitEverPsBlogConf')) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }

        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }

        // Display confirmations
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }

        $ever_blog_token = Tools::encrypt('everpsblog/cron');
        $emptytrash = $this->context->link->getModuleLink(
            $this->name,
            'emptytrash',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int)$this->context->shop->id
            ),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $pending = $this->context->link->getModuleLink(
            $this->name,
            'pending',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int)$this->context->shop->id
            ),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $planned = $this->context->link->getModuleLink(
            $this->name,
            'planned',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int)$this->context->shop->id
            ),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $sitemap_link = $this->context->link->getModuleLink(
            $this->name,
            'sitemaps',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int)$this->context->shop->id
            ),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $default_blog = $this->context->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $this->context->smarty->assign(array(
            'blog_sitemaps' => $this->getSitemapIndexes(),
            'image_dir' => $this->_path.'views/img',
            'everpsblogcron' => $emptytrash,
            'everpsblogcronpending' => $pending,
            'everpsblogcronplanned' => $planned,
            'everpsblogcronsitemap' => $sitemap_link,
            'blog_url' => $default_blog,
        ));

        if ($this->checkLatestEverModuleVersion($this->name, $this->version)) {
            $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/upgrade.tpl');
        }
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/footer.tpl');

        return $this->html;
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submitEverPsBlogConf')) {
            if (!Tools::getValue('EVERPSBLOG_ROUTE')
                || !Validate::isLinkRewrite(Tools::getValue('EVERPSBLOG_ROUTE'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Blog route" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_EXCERPT')
                || !Validate::isInt(Tools::getValue('EVERPSBLOG_EXCERPT'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Excerpt length" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_TITLE_LENGTH')
                || !Validate::isInt(Tools::getValue('EVERPSBLOG_TITLE_LENGTH'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Title length" is not valid');
            }
            if (Tools::getValue('EVERBLOG_SHOW_POST_COUNT')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_POST_COUNT'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Show post count" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_PAGINATION')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_PAGINATION'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts per page" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_HOME_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_HOME_NBR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts for home" is not valid');
            }
            if (!Tools::getValue('EVERPSBLOG_PRODUCT_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_PRODUCT_NBR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts for product" is not valid');
            }
            if (!Tools::getValue('EVERBLOG_ADMIN_EMAIL')
                || !Validate::isUnsignedInt(Tools::getValue('EVERBLOG_ADMIN_EMAIL'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Admin email" is not valid');
            }
            if (Tools::getValue('EVERBLOG_ALLOW_COMMENTS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ALLOW_COMMENTS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Allow comments" is not valid');
            }
            if (Tools::getValue('EVERBLOG_CHECK_COMMENTS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_CHECK_COMMENTS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Check comments" is not valid');
            }
            if (Tools::getValue('EVERBLOG_RSS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_RSS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Use RSS feed" is not valid');
            }
            if (Tools::getValue('EVERBLOG_SHOW_AUTHOR')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_AUTHOR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Show author" is not valid');
            }
            if (Tools::getValue('EVERBLOG_BANNED_USERS')
                && !Validate::isGenericName(Tools::getValue('EVERBLOG_BANNED_USERS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Banned users" is not valid');
            }
            if (Tools::getValue('EVERBLOG_BANNED_IP')
                && !Validate::isGenericName(Tools::getValue('EVERBLOG_BANNED_IP'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Banned IP" is not valid');
            }
            if (Tools::getValue('EVERBLOG_ONLY_LOGGED_COMMENT')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ONLY_LOGGED_COMMENT'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Only logged can comment" is not valid');
            }
            if (!Tools::getValue('EVERBLOG_EMPTY_TRASH')
                && !Validate::isUnsignedInt(Tools::getValue('EVERBLOG_FANCYBOX'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Fancybox" is not valid'
                );
            }
            if (!Tools::getValue('EVERPSBLOG_TYPE')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_TYPE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Default blog type" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ANIMATE')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ANIMATE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Use cool CSS" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_RELATED_POST')
                && !Validate::isBool(Tools::getValue('EVERBLOG_RELATED_POST'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show related posts on product page" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_SHOW_FEAT_CAT')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_FEAT_CAT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show featured category image" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_SHOW_FEAT_TAG')
                && !Validate::isBool(Tools::getValue('EVERBLOG_SHOW_FEAT_TAG'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show featured tag image" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ARCHIVE_COLUMNS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ARCHIVE_COLUMNS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show archives on columns" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_TAG_COLUMNS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_TAG_COLUMNS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show tags on columns" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_CATEG_COLUMNS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_CATEG_COLUMNS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show categories on columns" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_FANCYBOX')
                && !Validate::isBool(Tools::getValue('EVERBLOG_FANCYBOX'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Fancybox" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_CAT_FEATURED')
                && !Validate::isUnsignedInt(Tools::getValue('EVERBLOG_CAT_FEATURED'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Featured category" is not valid'
                );
            }
            // Multilingual fields
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('EVERBLOG_TITLE_'.$lang['id_lang'])
                    && !Validate::isString(Tools::getValue('EVERBLOG_TITLE_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog title is invalid'
                    );
                }
                if (Tools::getValue('EVERBLOG_META_DESC_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERBLOG_META_DESC_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog meta description is invalid'
                    );
                }
                if (Tools::getValue('EVERBLOG_TOP_TEXT_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERBLOG_TOP_TEXT_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog top text is invalid'
                    );
                }
                if (Tools::getValue('EVERBLOG_BOTTOM_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERBLOG_BOTTOM_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error : Blog bottom text is invalid'
                    );
                }
            }
            // Layouts
            if (Tools::getValue('EVERPSBLOG_BLOG_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_BLOG_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Blog layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_POST_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_POST_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Post layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_CAT_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_CAT_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Category layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_AUTHOR_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_AUTHOR_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Author layout" is not valid'
                );
            }
            if (Tools::getValue('EVERPSBLOG_TAG_LAYOUT')
                && !Validate::isString(Tools::getValue('EVERPSBLOG_TAG_LAYOUT'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Tag layout" is not valid'
                );
            }
            if (isset($_FILES['wordpress_xml'])
                && isset($_FILES['wordpress_xml']['tmp_name'])
                && !empty($_FILES['wordpress_xml']['tmp_name'])
            ) {
                if (pathinfo($_FILES['wordpress_xml']['name'], PATHINFO_EXTENSION) != 'xml') {
                    $this->postErrors[] = $this->l(
                        'Error : The field "Tag layout" is not valid'
                    );
                } else {
                    $this->importWordPressFile($_FILES['wordpress_xml']);
                }
            }
            if (Tools::getValue('EVERBLOG_IMPORT_POST_STATE')
                && !Validate::isString(Tools::getValue('EVERBLOG_IMPORT_POST_STATE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Default post status on import from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_IMPORT_AUTHORS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_IMPORT_AUTHORS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Import authors from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_IMPORT_CATS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_IMPORT_CATS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Import categories from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_IMPORT_TAGS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_IMPORT_TAGS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Import tags from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ENABLE_AUTHORS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ENABLE_AUTHORS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Enable authors from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ENABLE_CATS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ENABLE_CATS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Enable categories from WordPress xml file" is not valid'
                );
            }
            if (Tools::getValue('EVERBLOG_ENABLE_TAGS')
                && !Validate::isBool(Tools::getValue('EVERBLOG_ENABLE_TAGS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Enable tags from WordPress xml file" is not valid'
                );
            }
        }
    }

    protected function postProcess()
    {
        $this->checkHooks();
        $form_values = $this->getConfigFormValues();
        // Reset hooks
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-blog');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-category');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-post');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-tag');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-author');
        Hook::exec('hookModuleRoutes');
        // Preparing multilingual datas
        $everblog_title = array();
        $everblog_meta_desc = array();
        $everblog_top_text = array();
        $everblog_bottom_text = array();
        foreach (Language::getLanguages(false) as $lang) {
            $everblog_title[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            ) : '';
            $everblog_meta_desc[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            ) : '';
            $everblog_top_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            ) : '';
            $everblog_bottom_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            ) : '';
        }
        // Save all datas
        foreach (array_keys($form_values) as $key) {
            if ($key == 'EVERBLOG_TITLE') {
                Configuration::updateValue(
                    $key,
                    $everblog_title
                );
            } elseif ($key == 'EVERBLOG_META_DESC') {
                Configuration::updateValue(
                    $key,
                    $everblog_meta_desc
                );
            } elseif ($key == 'EVERBLOG_TOP_TEXT') {
                Configuration::updateValue(
                    $key,
                    $everblog_top_text,
                    true
                );
            } elseif ($key == 'EVERBLOG_BOTTOM_TEXT') {
                Configuration::updateValue(
                    $key,
                    $everblog_bottom_text,
                    true
                );
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
        $handle = fopen(
            _PS_MODULE_DIR_.'/'.$this->name.'/views/css/custom.css',
            'w+'
        );
        fclose($handle);
        /* Insert new values to the CSS file */
        file_put_contents(
            _PS_MODULE_DIR_.'/'.$this->name.'/views/css/custom.css',
            Tools::getValue('EVERBLOG_CSS')
        );

        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    protected function getConfigFormValues()
    {
        $custom_css = Tools::file_get_contents(
            _PS_MODULE_DIR_.'/'.$this->name.'/views/css/custom.css'
        );
        $formValues = array();
        $everblog_title = array();
        $everblog_meta_desc = array();
        $everblog_top_text = array();
        $everblog_bottom_text = array();
        foreach (Language::getLanguages(false) as $lang) {
            $everblog_title[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            ) : '';
            $everblog_meta_desc[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            ) : '';
            $everblog_top_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            ) : '';
            $everblog_bottom_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            ) : '';
        }
        $formValues[] = array(
            'EVERPSBLOG_ROUTE' => Configuration::get('EVERPSBLOG_ROUTE'),
            'EVERPSBLOG_EXCERPT' => Configuration::get('EVERPSBLOG_EXCERPT'),
            'EVERPSBLOG_TITLE_LENGTH' => Configuration::get('EVERPSBLOG_TITLE_LENGTH'),
            'EVERBLOG_SHOW_POST_COUNT' => Configuration::get('EVERBLOG_SHOW_POST_COUNT'),
            'EVERPSBLOG_PAGINATION' => Configuration::get('EVERPSBLOG_PAGINATION'),
            'EVERPSBLOG_HOME_NBR' => Configuration::get('EVERPSBLOG_HOME_NBR'),
            'EVERPSBLOG_PRODUCT_NBR' => Configuration::get('EVERPSBLOG_PRODUCT_NBR'),
            'EVERBLOG_ADMIN_EMAIL' => Configuration::get('EVERBLOG_ADMIN_EMAIL'),
            'EVERBLOG_ALLOW_COMMENTS' => Configuration::get('EVERBLOG_ALLOW_COMMENTS'),
            'EVERBLOG_CHECK_COMMENTS' => Configuration::get('EVERBLOG_CHECK_COMMENTS'),
            'EVERBLOG_RSS' => Configuration::get('EVERBLOG_RSS'),
            'EVERBLOG_SHOW_AUTHOR' => Configuration::get('EVERBLOG_SHOW_AUTHOR'),
            'EVERBLOG_BANNED_USERS' => Configuration::get('EVERBLOG_BANNED_USERS'),
            'EVERBLOG_BANNED_IP' => Configuration::get('EVERBLOG_BANNED_IP'),
            'EVERBLOG_ONLY_LOGGED_COMMENT' => Configuration::get('EVERBLOG_ONLY_LOGGED_COMMENT'),
            'EVERBLOG_EMPTY_TRASH' => Configuration::get('EVERBLOG_EMPTY_TRASH'),
            'EVERPSBLOG_TYPE' => Configuration::get('EVERPSBLOG_TYPE'),
            'EVERBLOG_ANIMATE' => Configuration::get('EVERBLOG_ANIMATE'),
            'EVERBLOG_RELATED_POST' => Configuration::get('EVERBLOG_RELATED_POST'),
            'EVERBLOG_SHOW_FEAT_CAT' => Configuration::get('EVERBLOG_SHOW_FEAT_CAT'),
            'EVERBLOG_SHOW_FEAT_TAG' => Configuration::get('EVERBLOG_SHOW_FEAT_TAG'),
            'EVERBLOG_ARCHIVE_COLUMNS' => Configuration::get('EVERBLOG_ARCHIVE_COLUMNS'),
            'EVERBLOG_TAG_COLUMNS' => Configuration::get('EVERBLOG_TAG_COLUMNS'),
            'EVERBLOG_CATEG_COLUMNS' => Configuration::get('EVERBLOG_CATEG_COLUMNS'),
            'EVERBLOG_FANCYBOX' => Configuration::get('EVERBLOG_FANCYBOX'),
            'EVERBLOG_CAT_FEATURED' => Configuration::get('EVERBLOG_CAT_FEATURED'),
            'EVERBLOG_TITLE' => (!empty(
                $everblog_title[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_title : Configuration::getInt(
                'EVERBLOG_TITLE'
            ),
            'EVERBLOG_META_DESC' => (!empty(
                $everblog_meta_desc[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_meta_desc : Configuration::getInt(
                'EVERBLOG_META_DESC'
            ),
            'EVERBLOG_TOP_TEXT' => (!empty(
                $everblog_top_text[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_top_text : Configuration::getInt(
                'EVERBLOG_TOP_TEXT'
            ),
            'EVERBLOG_BOTTOM_TEXT' => (!empty(
                $everblog_bottom_text[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_bottom_text : Configuration::getInt(
                'EVERBLOG_BOTTOM_TEXT'
            ),
            'EVERPSBLOG_BLOG_LAYOUT' => Configuration::get('EVERPSBLOG_BLOG_LAYOUT'),
            'EVERPSBLOG_POST_LAYOUT' => Configuration::get('EVERPSBLOG_POST_LAYOUT'),
            'EVERPSBLOG_CAT_LAYOUT' => Configuration::get('EVERPSBLOG_CAT_LAYOUT'),
            'EVERPSBLOG_AUTHOR_LAYOUT' => Configuration::get('EVERPSBLOG_AUTHOR_LAYOUT'),
            'EVERPSBLOG_TAG_LAYOUT' => Configuration::get('EVERPSBLOG_TAG_LAYOUT'),
            'EVERBLOG_CSS' => $custom_css,
            'EVERBLOG_CSS_FILE' => Configuration::get('EVERBLOG_CSS_FILE'),
            'EVERBLOG_IMPORT_AUTHORS' => Configuration::get('EVERBLOG_IMPORT_AUTHORS'),
            'EVERBLOG_IMPORT_CATS' => Configuration::get('EVERBLOG_IMPORT_CATS'),
            'EVERBLOG_IMPORT_TAGS' => Configuration::get('EVERBLOG_IMPORT_TAGS'),
            'EVERBLOG_ENABLE_AUTHORS' => Configuration::get('EVERBLOG_ENABLE_AUTHORS'),
            'EVERBLOG_ENABLE_CATS' => Configuration::get('EVERBLOG_ENABLE_CATS'),
            'EVERBLOG_ENABLE_TAGS' => Configuration::get('EVERBLOG_ENABLE_TAGS'),
            'EVERBLOG_IMPORT_POST_STATE' => Configuration::get('EVERBLOG_IMPORT_POST_STATE'),
            'wordpress_xml' => ''
        );
        $values = call_user_func_array('array_merge', $formValues);
        return $values;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEverPsBlogConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int)$this->context->language->id,
        );

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        // TODO : add default blog text per lang ?
        $employees = Employee::getEmployeesByProfile(
            1,
            true
        );
        $default_snippet = array(
            array(
                'snippet' => 'Article',
                'name' => $this->l('Simple article')
            ),
            array(
                'snippet' => 'NewsArticle',
                'name' => $this->l('News article')
            ),
        );
        $layouts = array(
            array(
                'layout' => 'layouts/layout-full-width.tpl',
                'name' => $this->l('Full width')
            ),
            array(
                'layout' => 'layouts/layout-left-column.tpl',
                'name' => $this->l('Left column')
            ),
            array(
                'layout' => 'layouts/layout-right-column.tpl',
                'name' => $this->l('Right column')
            ),
            array(
                'layout' => 'layouts/layout-both-columns.tpl',
                'name' => $this->l('Both columns')
            ),
        );
        $trash_days = array(
            array(
                'id_trash' => 0,
                'name' => $this->l('Do not empty trash')
            ),
            array(
                'id_trash' => 1,
                'name' => $this->l('One day')
            ),
            array(
                'id_trash' => 2,
                'name' => $this->l('Two days')
            ),
            array(
                'id_trash' => 3,
                'name' => $this->l('Three days')
            ),
            array(
                'id_trash' => 4,
                'name' => $this->l('Four days')
            ),
            array(
                'id_trash' => 5,
                'name' => $this->l('Five days')
            ),
            array(
                'id_trash' => 6,
                'name' => $this->l('Six days')
            ),
            array(
                'id_trash' => 7,
                'name' => $this->l('One week')
            ),
        );
        $css_files = array(
            array(
                'id_file' => 'default',
                'name' => $this->l('default.css file')
            ),
            array(
                'id_file' => 'red',
                'name' => $this->l('red.css file')
            ),
            array(
                'id_file' => 'green',
                'name' => $this->l('green.css file')
            ),
            array(
                'id_file' => 'yellow',
                'name' => $this->l('yellow.css file')
            ),
            array(
                'id_file' => 'white',
                'name' => $this->l('white.css file')
            ),
        );
        $post_status = array(
            array(
                'id_status' => 'draft',
                'name' => $this->l('draft')
            ),
            array(
                'id_status' => 'pending',
                'name' => $this->l('pending')
            ),
            array(
                'id_status' => 'published',
                'name' => $this->l('published')
            ),
            array(
                'id_status' => 'trash',
                'name' => $this->l('trash')
            ),
            array(
                'id_status' => 'planned',
                'name' => $this->l('planned')
            ),
        );
        $form_fields = array();
        $form_fields[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Blog default Settings'),
                    'icon' => 'icon-smile',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Blog base route'),
                        'name' => 'EVERPSBLOG_ROUTE',
                        'desc' => $this->l('Leaving empty will set "blog"'),
                        'hint' => $this->l('Use a keyword associated to your shop'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Post content excerpt'),
                        'name' => 'EVERPSBLOG_EXCERPT',
                        'desc' => $this->l('Post excerpt length for content on listing'),
                        'hint' => $this->l('Please set post content excerpt'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Post title length'),
                        'name' => 'EVERPSBLOG_TITLE_LENGTH',
                        'desc' => $this->l('Post title length for content on listing'),
                        'hint' => $this->l('Please set post title length'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show post views count ?'),
                        'desc' => $this->l('Set yes to show views count'),
                        'hint' => $this->l('Else will only be shown on admin'),
                        'required' => false,
                        'name' => 'EVERBLOG_SHOW_POST_COUNT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of posts for home'),
                        'name' => 'EVERPSBLOG_HOME_NBR',
                        'desc' => $this->l('Leaving empty will set 4 posts'),
                        'hint' => $this->l('Posts are 4 per row'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of posts for product'),
                        'name' => 'EVERPSBLOG_PRODUCT_NBR',
                        'desc' => $this->l('Leaving empty will set 4 posts'),
                        'hint' => $this->l('Posts are 4 per row'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Posts per page'),
                        'name' => 'EVERPSBLOG_PAGINATION',
                        'desc' => $this->l('Leaving empty will set 10 posts per page'),
                        'hint' => $this->l('Will add pagination'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Admin email'),
                        'desc' => $this->l('Will receive new comments notification by email'),
                        'hint' => $this->l('You can set a new account on your shop'),
                        'required' => true,
                        'name' => 'EVERBLOG_ADMIN_EMAIL',
                        'options' => array(
                            'query' => $employees,
                            'id' => 'id_employee',
                            'name' => 'email'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Allow comments on posts ?'),
                        'desc' => $this->l('Set yes to allow comments'),
                        'hint' => $this->l('You can check them before publishing'),
                        'required' => false,
                        'name' => 'EVERBLOG_ALLOW_COMMENTS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Check comments on posts before they are published ?'),
                        'desc' => $this->l('Set yes to check comments before publishing'),
                        'hint' => $this->l('In order to avoid spam'),
                        'required' => false,
                        'name' => 'EVERBLOG_CHECK_COMMENTS',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Allow only registered customers to comment ?'),
                        'desc' => $this->l('Set yes to allow only registered customers to comment'),
                        'hint' => $this->l('Else everyone will be able to comment'),
                        'required' => false,
                        'name' => 'EVERBLOG_ONLY_LOGGED_COMMENT',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Empty trash'),
                        'desc' => $this->l('Please choose auto empty trash in days'),
                        'hint' => $this->l('Will auto delete trashed posts on CRON task'),
                        'required' => true,
                        'name' => 'EVERBLOG_EMPTY_TRASH',
                        'options' => array(
                            'query' => $trash_days,
                            'id' => 'id_trash',
                            'name' => 'name',
                        ),
                        'lang' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Default blog SEO title'),
                        'name' => 'EVERBLOG_TITLE',
                        'desc' => $this->l('Max 65 characters for SEO'),
                        'hint' => $this->l('Will impact SEO'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Default blog SEO meta description'),
                        'name' => 'EVERBLOG_META_DESC',
                        'desc' => $this->l('Max 165 characters for SEO'),
                        'hint' => $this->l('Will impact SEO'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default blog type'),
                        'desc' => $this->l('Will be used for structured metadatas'),
                        'hint' => $this->l('Select blog type depending on your posts'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_TYPE',
                        'options' => array(
                            'query' => $default_snippet,
                            'id' => 'snippet',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Default blog top text'),
                        'name' => 'EVERBLOG_TOP_TEXT',
                        'desc' => $this->l('Will be shown on blog top default page'),
                        'hint' => $this->l('Explain your blog purpose'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                        'autoload_rte' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Default blog bottom text'),
                        'name' => 'EVERBLOG_BOTTOM_TEXT',
                        'desc' => $this->l('Will be shown on blog bottom default page'),
                        'hint' => $this->l('Explain your blog purpose'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                        'autoload_rte' => true
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use RSS feed ?'),
                        'desc' => $this->l('Will add a link to RSS feed on blog and each tag, category, author'),
                        'hint' => $this->l('Else feed wont be used'),
                        'required' => false,
                        'name' => 'EVERBLOG_RSS',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show author ?'),
                        'desc' => $this->l('Will show author name and avatar on posts'),
                        'hint' => $this->l('Else author name and avatar will be hidden'),
                        'required' => false,
                        'name' => 'EVERBLOG_SHOW_AUTHOR',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Banned users'),
                        'name' => 'EVERBLOG_BANNED_USERS',
                        'desc' => $this->l('Add banned users typing their emails, one per line'),
                        'hint' => $this->l('Unwanted users won\'t be able to post comments'),
                        'cols' => 36,
                        'rows' => 4,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Banned IP'),
                        'name' => 'EVERBLOG_BANNED_IP',
                        'desc' => $this->l('Add banned users typing their IP addresses, one per line'),
                        'hint' => $this->l('Unwanted users won\'t be able to post comments'),
                        'cols' => 36,
                        'rows' => 4,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show parent categories list on left/right columns ?'),
                        'desc' => $this->l('Set yes show a list of all parent categories on left or right columns'),
                        'hint' => $this->l('Will show ordered parent categories on left/right columns'),
                        'name' => 'EVERBLOG_CATEG_COLUMNS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show tags list on left/right columns ?'),
                        'desc' => $this->l('Set yes to activate cool stuff'),
                        'hint' => $this->l('Set yes show a tags cloud on left or right columns'),
                        'required' => false,
                        'name' => 'EVERBLOG_TAG_COLUMNS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show archives list on left/right columns ?'),
                        'desc' => $this->l('Set yes show links for monthly posts on left or right columns'),
                        'hint' => $this->l('Will show yearly and monthly posts'),
                        'required' => false,
                        'name' => 'EVERBLOG_ARCHIVE_COLUMNS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show related posts on products pages ?'),
                        'desc' => $this->l('Set yes show related posts on product pages footer'),
                        'hint' => $this->l('Will show related posts on product page footer'),
                        'required' => false,
                        'name' => 'EVERBLOG_RELATED_POST',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show featured images on categories ?'),
                        'desc' => $this->l('Set yes to show each category featured image'),
                        'hint' => $this->l('Else category featured image won\'t be shown'),
                        'name' => 'EVERBLOG_SHOW_FEAT_CAT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show featured images on tags ?'),
                        'desc' => $this->l('Set yes to show each tag featured image'),
                        'hint' => $this->l('Else tag featured image won\'t be shown'),
                        'name' => 'EVERBLOG_SHOW_FEAT_TAG',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate cool CSS animations ?'),
                        'desc' => $this->l('Set yes to activate cool stuff'),
                        'hint' => $this->l('Will add animations on posts, images, etc'),
                        'name' => 'EVERBLOG_ANIMATE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Fancybox'),
                        'hint' => $this->l('Set no if your theme already uses it'),
                        'desc' => $this->l('Use Fancybox for popups on post images'),
                        'name' => 'EVERBLOG_FANCYBOX',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Featured category on blog default page'),
                        'name' => 'EVERBLOG_CAT_FEATURED',
                        'desc' => $this->l('Featured category'),
                        'hint' => $this->l('Will show category products on blog page'),
                        'cols' => 36,
                        'rows' => 4,
                    ),
                ),
                'buttons' => array(
                    'generateBlogSitemap' => array(
                        'name' => 'submitGenerateBlogSitemap',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-refresh',
                        'title' => $this->l('Generate sitemaps')
                    ),
                ),
                'submit' => array(
                    'name' => 'submit',
                    'title' => $this->l('Save'),
                ),
            )
        );
        $form_fields[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Blog layout settings'),
                    'icon' => 'icon-smile',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default blog layout'),
                        'desc' => $this->l('Will add or remove columns from blog page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_BLOG_LAYOUT',
                        'options' => array(
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default post layout'),
                        'desc' => $this->l('Will add or remove columns from post page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_POST_LAYOUT',
                        'options' => array(
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default category layout'),
                        'desc' => $this->l('Will add or remove columns from category page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_CAT_LAYOUT',
                        'options' => array(
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default author layout'),
                        'desc' => $this->l('Will add or remove columns from author page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_AUTHOR_LAYOUT',
                        'options' => array(
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default tag layout'),
                        'desc' => $this->l('Will add or remove columns from tag page'),
                        'hint' => $this->l('You can add or remove modules from Prestashop positions'),
                        'required' => true,
                        'name' => 'EVERPSBLOG_TAG_LAYOUT',
                        'options' => array(
                            'query' => $layouts,
                            'id' => 'layout',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'name' => 'submit',
                    'title' => $this->l('Save'),
                ),
            )
        );
        $form_fields[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('WordPress XML import settings'),
                    'icon' => 'icon-smile',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default post state on XML import'),
                        'desc' => $this->l('Will set default post state on XML import'),
                        'hint' => $this->l('Please select default post state on XML file import'),
                        'required' => true,
                        'name' => 'EVERBLOG_IMPORT_POST_STATE',
                        'options' => array(
                            'query' => $post_status,
                            'id' => 'id_status',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Import WordPress authors from xml file ?'),
                        'desc' => $this->l('Set yes to import WordPress authors'),
                        'hint' => $this->l('Else no authors will be imported'),
                        'required' => false,
                        'name' => 'EVERBLOG_IMPORT_AUTHORS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Import WordPress categories from xml file ?'),
                        'desc' => $this->l('Set yes to import WordPress categories'),
                        'hint' => $this->l('Else no categories will be imported'),
                        'required' => false,
                        'name' => 'EVERBLOG_IMPORT_CATS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Import WordPress tags from xml file ?'),
                        'desc' => $this->l('Set yes to import WordPress tags'),
                        'hint' => $this->l('Else no tags will be imported'),
                        'required' => false,
                        'name' => 'EVERBLOG_IMPORT_TAGS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable WordPress authors from xml file ?'),
                        'desc' => $this->l('Set yes to enable WordPress authors'),
                        'hint' => $this->l('Else no authors will be enabled'),
                        'required' => false,
                        'name' => 'EVERBLOG_ENABLE_AUTHORS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable WordPress categories from xml file ?'),
                        'desc' => $this->l('Set yes to enable WordPress categories'),
                        'hint' => $this->l('Else no categories will be enabled'),
                        'required' => false,
                        'name' => 'EVERBLOG_ENABLE_CATS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable WordPress tags from xml file ?'),
                        'desc' => $this->l('Set yes to enable WordPress tags'),
                        'hint' => $this->l('Else no tags will be enabled'),
                        'required' => false,
                        'name' => 'EVERBLOG_ENABLE_TAGS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Import WordPress XML file'),
                        'desc' => $this->l('Import WordPress XML posts file'),
                        'hint' => $this->l('Will import posts from WordPress XML file'),
                        'name' => 'wordpress_xml',
                        'required' => false
                    ),
                ),
                'submit' => array(
                    'name' => 'submit',
                    'title' => $this->l('Save and import'),
                ),
            )
        );
        $form_fields[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Design settings'),
                    'icon' => 'icon-smile',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Custom CSS file'),
                        'desc' => $this->l('You can change here default CSS file'),
                        'hint' => $this->l('By changing CSS file, you will change blog colors'),
                        'required' => true,
                        'name' => 'EVERBLOG_CSS_FILE',
                        'options' => array(
                            'query' => $css_files,
                            'id' => 'id_file',
                            'name' => 'name',
                        ),
                        'lang' => false,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Custom CSS for blog'),
                        'desc' => $this->l('Add here your custom CSS rules'),
                        'hint' => $this->l('Webdesigners here can manage CSS rules for blog'),
                        'name' => 'EVERBLOG_CSS',
                    ),
                ),
                'submit' => array(
                    'name' => 'submit',
                    'title' => $this->l('Save'),
                ),
            )
        );
        return $form_fields;
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path.'views/css/ever.css');
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJs($this->_path.'views/js/ever.js');
        }
    }

    public function hookBackofficeHeader()
    {
        return $this->hookActionAdminControllerSetMedia();
    }

    public function hookDisplayAdminAfterHeader()
    {
        if ($this->checkLatestEverModuleVersion($this->name, $this->version)) {
            return $this->context->smarty->fetch($this->local_path.'views/templates/admin/upgrade.tpl');
        }
    }

    public function hookHeader()
    {
        $controller_name = Tools::getValue('controller');
        $module_name = Tools::getValue('module');
        if ($module_name == 'everpsblog') {
            $this->context->controller->addCSS(
                $this->module_folder.'/views/css/everpsblog.css',
                'all'
            );
            $this->context->controller->addCSS(
                $this->module_folder.'everpsblog/views/css/everpsblog.css',
                'all'
            );
            $this->context->controller->addJs(
                $this->_path.'views/js/everpsblog.js'
            );
            if ($controller_name == 'post') {
                if ((int)Configuration::get('EVERBLOG_FANCYBOX')) {
                    if ($controller_name != 'order') {
                        $this->context->controller->addCSS(($this->_path).'views/css/jquery.fancybox.min.css', 'all');
                        $this->context->controller->addJS(($this->_path).'views/js/jquery.fancybox.min.js', 'all');
                    }
                }
            }
        }
        $this->context->controller->addCSS(
            $this->module_folder.'/views/css/everpsblog-columns.css',
            'all'
        );
        $this->context->controller->addCSS(
            $this->module_folder.'/views/css/everpsblog-all.css',
            'all'
        );
        $css_file = Configuration::get('EVERBLOG_CSS_FILE');
        if ($css_file && $css_file != 'default') {
            $this->context->controller->addCSS(
                $this->module_folder.'/views/css/'.$css_file.'.css',
                'all'
            );
        }
        if (file_exists($this->module_folder.'/views/css/custom.css')) {
            $this->context->controller->addCSS(
                $this->module_folder.'/views/css/custom.css',
                'all'
            );
        }
    }

    public function hookDisplayLeftColumn($params)
    {
        if ((int)Configuration::get('EVERPSBLOG_HOME_NBR')) {
            $post_number = (int)Configuration::get('EVERPSBLOG_HOME_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true
        );
        $tags = EverPsBlogTag::getAllTags(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $categories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $latest_posts = EverPsBlogPost::getLatestPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            0,
            (int)$post_number
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $showArchives = Configuration::get(
            'EVERBLOG_ARCHIVE_COLUMNS'
        );
        $showCategories = Configuration::get(
            'EVERBLOG_CATEG_COLUMNS'
        );
        $showTags = Configuration::get(
            'EVERBLOG_TAG_COLUMNS'
        );
        $this->context->smarty->assign(array(
            'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
            'everpsblog' => $latest_posts,
            'showArchives' => $showArchives,
            'showCategories' => $showCategories,
            'showTags' => $showTags,
            'blogUrl' => $blogUrl,
            'tags' => $tags,
            'categories' => $categories,
            'animate' => $animate,
            'blogImg_dir' => $this->siteUrl.'/modules/everpsblog/views/img/',
        ));
        return $this->display(__FILE__, 'views/templates/hook/columns.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayContainerBottom2()
    {
        return $this->hookDisplayHome();
    }

    public function hookDisplayHome()
    {
        if ((int)Configuration::get('EVERPSBLOG_HOME_NBR') > 0) {
            $post_number = (int)Configuration::get('EVERPSBLOG_HOME_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true
        );
        $latest_posts = EverPsBlogPost::getLatestPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            0,
            (int)$post_number
        );
        if (!$latest_posts || !count($latest_posts)) {
            return;
        }
        $evercategories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $this->context->smarty->assign(
            array(
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blogUrl' => $blogUrl,
                'everpsblog' => $latest_posts,
                'evercategory' => $evercategories,
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => $this->siteUrl.'/modules/everpsblog/views/img/',
                'animated' => $animate,
            )
        );
        return $this->display(__FILE__, 'views/templates/hook/home.tpl');
    }

    public function hookDisplayCustomerAccount()
    {
        if ((bool)Configuration::get('EVERBLOG_ALLOW_COMMENTS') === true) {
            return $this->display(__FILE__, 'views/templates/hook/my-account.tpl');
        }
    }

    public function hookDisplayMyAccountBlock($params)
    {
        return $this->hookDisplayCustomerAccount();
    }

    public function hookDisplayFooterProduct()
    {
        if ((bool)Configuration::get('EVERBLOG_RELATED_POST') === false) {
            return;
        }
        if ((int)Configuration::get('EVERPSBLOG_PRODUCT_NBR')) {
            $post_number = (int)Configuration::get('EVERPSBLOG_PRODUCT_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true
        );
        $posts = EverPsBlogPost::getPostsByProduct(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            (int)Tools::getValue('id_product'),
            0,
            (int)$post_number
        );
        if (!$posts
            || !count($posts)
        ) {
            return;
        }
        $evercategories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $everpsblog = array();
        foreach ($posts as $post) {
            $post->title = EverPsBlogPost::changeShortcodes(
                $post->title,
                (int)Context::getContext()->customer->id
            );
            $post->content = EverPsBlogPost::changeShortcodes(
                $post->content,
                (int)Context::getContext()->customer->id
            );
            $everpsblog[] = $post;
        }
        $this->context->smarty->assign(
            array(
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blogUrl' => $blogUrl,
                'everpsblog' => $everpsblog,
                'evercategory' => $evercategories,
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => $this->siteUrl.'/modules/everpsblog/views/img/',
                'animated' => $animate,
            )
        );
        return $this->display(__FILE__, 'views/templates/hook/product.tpl');
    }

    public function hookDisplayFooter()
    {
        return $this->hookDisplayBeforeBodyClosingTag();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        $controller_name = Tools::getValue('controller');
        $module_name = Tools::getValue('module');

        if ($module_name == 'everpsblog') {
            if ($controller_name == 'post') {
                $this->context->smarty->assign(
                    array(
                        'everfancybox' => (bool)Configuration::get('EVERBLOG_FANCYBOX'),
                    )
                );
                return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
            }
        }
    }

    public function hookActionOutputHTMLBefore($params)
    {
        $regex = '/<p>\[everpsblog\s+id=\s*[\'\"]?(\d+)[\'\"]?\s*\]<\/p>|\[everpsblog\s+id=\s*[\'\"]?(\d+)[\'\"]?\s*\]/Us';
        if (preg_match_all($regex, $params['html'], $matches)) {
            if ($html = preg_replace_callback($regex, array($this, 'displayPostsByCatId'), $params['html'])) {
                $params['html'] = $html;
            }
        }
        $regex_product_cat = '/<p>\[everpsblog\s+productcat=\s*[\'\"]?(\d+)[\'\"]?\s*\]<\/p>|\[everpsblog\s+productcat=\s*[\'\"]?(\d+)[\'\"]?\s*\]/Us';
        if (preg_match_all($regex_product_cat, $params['html'], $matches)) {
            if ($html = preg_replace_callback($regex_product_cat, array($this, 'displayProductsByCatId'), $params['html'])) {
                $params['html'] = $html;
            }
        }
        $regex_product = '/<p>\[everpsblog\s+productid=\s*[\'\"]?(\d+)[\'\"]?\s*\]<\/p>|\[everpsblog\s+productid=\s*[\'\"]?(\d+)[\'\"]?\s*\]/Us';
        if (preg_match_all($regex_product, $params['html'], $matches)) {
            if ($html = preg_replace_callback($regex_product, array($this, 'displayProductById'), $params['html'])) {
                $params['html'] = $html;
            }
        }
        $params['html'] = EverPsBlogPost::changeShortcodes(
            $params['html']
        );
    }

    public function hookActionFrontControllerAfterInit()
    {
        foreach (Shop::getShops() as $shop) {
            $this->publishPlannedPosts(
                (int)$shop['id_shop']
            );
            $this->emptyTrash(
                (int)$shop['id_shop']
            );
        }
    }

    public function displayPostsByCatId($shortcode)
    {
        if ((int)Configuration::get('EVERPSBLOG_PRODUCT_NBR') > 0) {
            $post_number = (int)Configuration::get('EVERPSBLOG_PRODUCT_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true
        );
        $post_category = new EverPsBlogCategory(
            (int)$shortcode[1],
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $latest_posts = EverPsBlogPost::getPostsByCategory(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            (int)$shortcode[1],
            0,
            (int)$post_number
        );
        if (!$latest_posts || !count($latest_posts)) {
            return;
        }
        $evercategories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $this->context->smarty->assign(
            array(
                'post_category' => $post_category,
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blogUrl' => $blogUrl,
                'everpsblog' => $latest_posts,
                'evercategory' => $evercategories,
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => $this->siteUrl.'/modules/everpsblog/views/img/',
                'animated' => $animate,
            )
        );
        return $this->display(__FILE__, 'views/templates/hook/cat_shortcode.tpl');
    }

    public function displayProductsByCatId($shortcode)
    {
        $featured_category = new Category(
            (int)$shortcode[1],
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $featured_products = $featured_category->getProducts(
            (int)$this->context->language->id,
            1,
            (int)Configuration::get('EVERPSBLOG_PRODUCT_NBR')
        );
        if (!empty($featured_products)) {
            $showPrice = true;
            $assembler = new ProductAssembler(Context::getContext());
            $presenterFactory = new ProductPresenterFactory(Context::getContext());
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            $productsForTemplate = array();

            $presentationSettings->showPrices = $showPrice;

            if (is_array($featured_products)) {
                foreach ($featured_products as $productId) {
                    $productsForTemplate[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct(array('id_product' => $productId['id_product'])),
                        $this->context->language
                    );
                }
            }
            $this->context->smarty->assign(array(
                'everpsblog_category' => $featured_category,
                'everpsblog_products' => $productsForTemplate,
            ));
            return $this->display(__FILE__, 'views/templates/hook/products_shortcode.tpl');
        }
    }

    public function displayProductById($shortcode)
    {
        $product = new Product(
            (int)$shortcode[1],
            false,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        if (!Validate::isLoadedObject($product)) {
            return;
        }
        $category = new Category(
            (int)$product->id_category_default,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        if (!$category->checkAccess((int)Context::getContext()->customer->id)) {
            return;
        }
        if ((bool)$product->active === true) {
            $showPrice = true;
            $assembler = new ProductAssembler(Context::getContext());
            $presenterFactory = new ProductPresenterFactory(Context::getContext());
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            $presentationSettings->showPrices = $showPrice;

            $productForTemplate = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct(array('id_product' => $product->id)),
                $this->context->language
            );
            $this->context->smarty->assign(array(
                'everpsblog_product' => $productForTemplate,
            ));
            return $this->display(__FILE__, 'views/templates/hook/product_shortcode.tpl');
        }
    }

    public function emptyTrash($id_shop)
    {
        $return = false;
        $days = (int)Configuration::get('EVERBLOG_EMPTY_TRASH');
        foreach (Language::getLanguages(false) as $language) {
            $posts = EverPsBlogPost::getPosts(
                (int)$language['id_lang'],
                (int)$id_shop,
                0,
                null,
                (string)'trash'
            );
            if (!$posts) {
                return true;
            }
            foreach ($posts as $trash_post) {
                if ((strtotime($trash_post['date_upd']) >= strtotime('-'.$days.' days'))
                ) {
                    $post = new EverPsBlogPost(
                        (int)$trash_post['id_ever_post']
                    );
                    if ($post->delete()) {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

    public function sendPendingNotification($id_shop)
    {
        $employee = new Employee(
            (int)Configuration::get('EVERBLOG_ADMIN_EMAIL')
        );
        $posts = EverPsBlogPost::getPosts(
            (int)$employee->id_lang,
            (int)$id_shop,
            0,
            0,
            'pending'
        );
        if (!count($posts)) {
            return true;
        }
        $post_list = '';
        foreach ($posts as $pending) {
            $post = new EverPsBlogPost(
                (int)$pending['id_ever_post'],
                (int)$employee->id_lang,
                (int)$id_shop
            );
            $post_list .= '<br/><p>'.$post->title.'</p>';
        }
        $mailDir = $this->module_folder.'/mails/';
        $everShopEmail = Configuration::get('PS_SHOP_EMAIL');
        $sent = Mail::send(
            (int)$this->context->language->id,
            'pending',
            $this->l('Review on pending posts'),
            array(
                '{shop_name}'=>Configuration::get('PS_SHOP_NAME'),
                '{shop_logo}'=>_PS_IMG_DIR_.Configuration::get(
                    'PS_LOGO',
                    null,
                    null,
                    (int)$this->context->shop->id
                ),
                '{posts}' => (string)$post_list
            ),
            (string)$employee->email,
            null,
            (string)$everShopEmail,
            Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            $mailDir,
            false,
            null,
            (string)$everShopEmail,
            (string)$everShopEmail,
            Configuration::get('PS_SHOP_NAME')
        );
        return $sent;
    }

    public function publishPlannedPosts($id_shop)
    {
        $context = Context::getContext();
        $posts = EverPsBlogPost::getPosts(
            (int)$context->language->id,
            (int)$id_shop,
            0,
            0,
            'planned'
        );
        if (!count($posts)) {
            return;
        }
        foreach ($posts as $planned) {
            $post = new EverPsBlogPost(
                (int)$planned['id_ever_post'],
                (int)$context->language->id,
                (int)$id_shop
            );
            if ($post->date_add <= date('Y-m-d H:i:s')) {
                $post->post_status = 'published';
                $post->save();
            }
        }
        return true;
    }

    public function hookActionObjectShopAddAfter($params)
    {
        $controllerTypes = array('admin', 'moduleadmin');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $shop = $params['object'];
        $root_category = new EverPsBlogCategory();
        $root_category->is_root_category = 1;
        $root_category->active = 1;
        $root_category->id_shop = (int)$shop->id;
        foreach (Language::getLanguages(false) as $language) {
            $root_category->title[$language['id_lang']] = 'Root';
            $root_category->content[$language['id_lang']] = 'Root';
            $root_category->link_rewrite[$language['id_lang']] = 'root';
        }
        $root_category->save();
    }

    public function hookActionObjectEverPsBlogPostAddAfter($params)
    {
        $controllerTypes = array('admin', 'moduleadmin');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        return $this->hookActionObjectEverPsBlogPostUpdateAfter($params);
    }

    public function hookActionObjectEverPsBlogPostUpdateAfter($params)
    {
        $controllerTypes = array('admin', 'moduleadmin');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $post_categories = EverPsBlogCleaner::convertToArray(
            json_decode($params['object']->post_categories, true)
        );
        $post_tags = EverPsBlogCleaner::convertToArray(
            json_decode($params['object']->post_tags, true)
        );
        $post_products = EverPsBlogCleaner::convertToArray(
            json_decode($params['object']->post_products, true)
        );
        // First drop post taxonomies
        EverPsBlogTaxonomy::dropTaxonomy(
            (int)$params['object']->id,
            'category'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int)$params['object']->id,
            'tag'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int)$params['object']->id,
            'product'
        );
        // Then insert taxonomies
        foreach ($post_categories as $id_post_category) {
            EverPsBlogTaxonomy::insertTaxonomy(
                (int)$id_post_category,
                (int)$params['object']->id,
                'category'
            );
        }
        foreach ($post_tags as $id_post_tag) {
            EverPsBlogTaxonomy::insertTaxonomy(
                (int)$id_post_tag,
                (int)$params['object']->id,
                'tag'
            );
        }
        foreach ($post_products as $id_post_product) {
            EverPsBlogTaxonomy::insertTaxonomy(
                (int)$id_post_product,
                (int)$params['object']->id,
                'product'
            );
        }
        // At least check root taxonomy
        EverPsBlogTaxonomy::checkDefaultPostCategory(
            $params['object']->id
        );
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        .'tmp/ever_blog_post_mini_'
        .(int)$params['object']->id
        .'_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogCategoryUpdateAfter($params)
    {
        $controllerTypes = array('admin', 'moduleadmin');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        .'tmp/ever_blog_category_mini_'
        .(int)$params['object']->id
        .'_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogTagUpdateAfter($params)
    {
        $controllerTypes = array('admin', 'moduleadmin');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        .'tmp/ever_blog_tag_mini_'
        .(int)$params['object']->id
        .'_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogAuthorUpdateAfter($params)
    {
        $controllerTypes = array('admin', 'moduleadmin');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        // Drop temp img
        $tmp_file = _PS_IMG_DIR_
        .'tmp/ever_blog_author_mini_'
        .(int)$params['object']->id
        .'_1.jpg';
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
        return $this->generateBlogSitemap();
    }

    public function hookActionObjectShopDeleteAfter($params)
    {
        $controllerTypes = array('admin', 'moduleadmin');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        $shop = $params['object'];

        Db::getInstance()->delete(
            'ever_blog_category',
            'id_shop = '.(int)$shop->id
        );
    }

    public function hookActionObjectEverPsBlogPostDeleteAfter($params)
    {
        $old_img = _PS_MODULE_DIR_
        .'everpsblog/views/img/posts/post_image_'
        .(int)$params['object']->id
        .'.jpg';
        $old_ps_img = _PS_IMG_DIR_
        .'posts/'
        .(int)$params['object']->id
        .'.jpg';
        if (file_exists($old_ps_img)) {
            unlink($old_ps_img);
        }
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        $image = EverPsBlogImage::getBlogImage(
            (int)$params['object']->id,
            (int)Context::getContext()->shop->id,
            'post'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        EverPsBlogTaxonomy::dropTaxonomy(
            (int)$params['object']->id,
            'category'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int)$params['object']->id,
            'tag'
        );
        EverPsBlogTaxonomy::dropTaxonomy(
            (int)$params['object']->id,
            'product'
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogCategoryDeleteAfter($params)
    {
        $old_img = _PS_MODULE_DIR_
        .'everpsblog/views/img/categories/category_image_'
        .(int)$params['object']->id
        .'.jpg';
        $old_ps_img = _PS_IMG_DIR_
        .'categories/'
        .(int)$params['object']->id
        .'.jpg';
        if (file_exists($old_ps_img)) {
            unlink($old_ps_img);
        }
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        $image = EverPsBlogImage::getBlogImage(
            (int)$params['object']->id,
            (int)Context::getContext()->shop->id,
            'category'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        EverPsBlogTaxonomy::dropCategoryTaxonomy(
            (int)$params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectEverPsBlogTagDeleteAfter($params)
    {
        $old_img = $this->module_folder.'/views/img/tags/tag_image_'.(int)$params['object']->id.'.jpg';
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        EverPsBlogTaxonomy::dropTagTaxonomy(
            (int)$params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectAuthorDeleteAfter($params)
    {
        $old_img = _PS_MODULE_DIR_
        .'everpsblog/views/img/authors/author_image_'
        .(int)$params['object']->id
        .'.jpg';
        $old_ps_img = _PS_IMG_DIR_
        .'authors/'
        .(int)$params['object']->id
        .'.jpg';
        if (file_exists($old_ps_img)) {
            unlink($old_ps_img);
        }
        if (file_exists($old_img)) {
            unlink($old_img);
        }
        $image = EverPsBlogImage::getBlogImage(
            (int)$params['object']->id,
            (int)Context::getContext()->shop->id,
            'author'
        );
        if (Validate::isLoadedObject($image)) {
            $image->delete();
        }
        EverPsBlogPost::dropBlogAuthorPosts(
            (int)$params['object']->id
        );
        // Sitemaps
        $this->generateBlogSitemap();
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        EverPsBlogTaxonomy::dropProductTaxonomy(
            (int)$params['object']->id
        );
    }

    public function generateBlogSitemap($id_shop = null, $cron = false)
    {
        set_time_limit(0);
        if (!$id_shop) {
            $id_shop = (int)$this->context->shop->id;
        }
        if (_PS_VERSION_ >= '1.6.1.7') {
            $languages = Language::getIDs(true);
        } else {
            $languages = $this->getLanguagesIds(true);
        }
        $result = false;
        foreach ($languages as $id_lang) {
            $result &= $this->processSitemapAuthor((int)$id_shop, (int)$id_lang);
            $result &= $this->processSitemapTag((int)$id_shop, (int)$id_lang);
            $result &= $this->processSitemapCategory((int)$id_shop, (int)$id_lang);
            $result &= $this->processSitemapPost((int)$id_shop, (int)$id_lang);
        }

        $this->postSuccess[] = $this->l('All XML sitemaps have been generated');
        if ((bool)$cron === true) {
            return $result;
        }
    }

    private function processSitemapPost($id_shop, $id_lang)
    {
        set_time_limit(0);
        $iso_lang = Language::getIsoById((int)$id_lang);

        $sitemap = new EverPsBlogSitemap(
            $this->siteUrl
        );
        $sitemap->setPath(_PS_ROOT_DIR_.'/');
        $sitemap->setFilename('blogpost_'.(int)$id_shop.'_lang_'.(string)$iso_lang);
        $sql =
            'SELECT id_ever_post FROM '._DB_PREFIX_.'ever_blog_post
            WHERE sitemap = 1 AND post_status = "published"';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $post = new EverPsBlogPost(
                    (int)$result['id_ever_post'],
                    (int)$this->context->language->id,
                    (int)$this->context->shop->id
                );

                $post_url = $link->getModuleLink(
                    'everpsblog',
                    'post',
                    array(
                        'id_ever_post' => $post->id,
                        'link_rewrite' => $post->link_rewrite
                    )
                );
                $sitemap->addItem(
                    $post_url,
                    1,
                    'weekly',
                    $post->date_upd
                );
            }
            return $sitemap->createSitemapIndex(
                $this->siteUrl,
                'Today'
            );
        }
    }

    private function processSitemapAuthor($id_shop, $id_lang)
    {
        set_time_limit(0);
        $iso_lang = Language::getIsoById((int)$id_lang);

        $sitemap = new EverPsBlogSitemap(
            $this->siteUrl
        );
        $sitemap->setPath(_PS_ROOT_DIR_.'/');
        $sitemap->setFilename('blogauthor_'.(int)$id_shop.'_lang_'.(string)$iso_lang);
        $sql =
            'SELECT id_ever_author FROM '._DB_PREFIX_.'ever_blog_author
            WHERE sitemap = 1 AND active = 1';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $author = new EverPsBlogAuthor(
                    (int)$result['id_ever_author'],
                    (int)$this->context->language->id,
                    (int)$this->context->shop->id
                );

                $author_url = $link->getModuleLink(
                    'everpsblog',
                    'author',
                    array(
                        'id_ever_author' => $author->id,
                        'link_rewrite' => $author->link_rewrite
                    )
                );
                if ((bool)$author->active === true) {
                    $sitemap->addItem(
                        $author_url,
                        1,
                        'weekly',
                        $author->date_upd
                    );
                }
            }
            return $sitemap->createSitemapIndex(
                $this->siteUrl,
                'Today'
            );
        }
    }

    private function processSitemapTag($id_shop, $id_lang)
    {
        set_time_limit(0);
        $iso_lang = Language::getIsoById((int)$id_lang);

        $sitemap = new EverPsBlogSitemap(
            $this->siteUrl
        );
        $sitemap->setPath(_PS_ROOT_DIR_.'/');
        $sitemap->setFilename('blogtag_'.(int)$id_shop.'_lang_'.(string)$iso_lang);
        $sql =
            'SELECT id_ever_tag FROM '._DB_PREFIX_.'ever_blog_tag
            WHERE sitemap = 1 AND active = 1';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $tag = new EverPsBlogTag(
                    (int)$result['id_ever_tag'],
                    (int)$this->context->language->id,
                    (int)$this->context->shop->id
                );

                $tag_url = $link->getModuleLink(
                    'everpsblog',
                    'tag',
                    array(
                        'id_ever_tag' => $tag->id,
                        'link_rewrite' => $tag->link_rewrite
                    )
                );
                if ((bool)$tag->active === true) {
                    $sitemap->addItem(
                        $tag_url,
                        1,
                        'weekly',
                        $tag->date_upd
                    );
                }
            }
            return $sitemap->createSitemapIndex(
                $this->siteUrl,
                'Today'
            );
        }
    }

    private function processSitemapCategory($id_shop, $id_lang)
    {
        set_time_limit(0);
        $iso_lang = Language::getIsoById((int)$id_lang);

        $sitemap = new EverPsBlogSitemap(
            $this->siteUrl
        );
        $sitemap->setPath(_PS_ROOT_DIR_.'/');
        $sitemap->setFilename('blogcategory_'.(int)$id_shop.'_lang_'.(string)$iso_lang);
        $sql =
            'SELECT id_ever_category FROM '._DB_PREFIX_.'ever_blog_category
            WHERE sitemap = 1 AND active = 1';
        if ($results = Db::getInstance()->executeS($sql)) {
            foreach ($results as $result) {
                $link = new Link();
                $category = new EverPsBlogCategory(
                    (int)$result['id_ever_category'],
                    (int)$this->context->language->id,
                    (int)$this->context->shop->id
                );

                $category_url = $link->getModuleLink(
                    'everpsblog',
                    'category',
                    array(
                        'id_ever_category' => $category->id,
                        'link_rewrite' => $category->link_rewrite
                    )
                );
                if ((bool)$category->active === true && (bool)$category->is_root_category === false) {
                    $sitemap->addItem(
                        $category_url,
                        1,
                        'weekly',
                        $category->date_upd
                    );
                }
            }
            return $sitemap->createSitemapIndex(
                $this->siteUrl,
                'Today'
            );
        }
    }

    public function getSitemapIndexes()
    {
        $indexes = array();
        $sitemap_indexes_dir = glob(_PS_ROOT_DIR_.'/*');
        foreach ($sitemap_indexes_dir as $index) {
            if (is_file($index)
                && pathinfo($index, PATHINFO_EXTENSION) == 'xml'
                && strpos(basename($index), 'index')
            ) {
                $indexes[] = $this->siteUrl.basename($index);
            }
        }
        return (array)$indexes;
    }

    public function hookActionAdminMetaAfterWriteRobotsFile($params)
    {
        $indexes = $this->getSitemapIndexes();
        // Panda theme uses random int on css file parameter
        $allowSitemap = 'Disallow: /modules/stthemeeditor/views/css'."\r\n";
        $allowSitemap .= "\n";
        if ($indexes) {
            foreach ($indexes as $index) {
                $allowSitemap .= 'Sitemap: '
                .$index
                ."\r\n";
            }
        }
        fwrite($params['write_fd'], "#Rules from everpsblog\n");
        fwrite($params['write_fd'], $allowSitemap);
    }

    /**
     * Register module blog and PS hooks
    */
    private function checkHooks()
    {
        $result = false;
        // Register blog hook
        $result &= $this->registerHook('actionAdminMetaAfterWriteRobotsFile');
        $result &= $this->registerHook('actionBeforeEverPostInitContent');
        $result &= $this->registerHook('actionBeforeEverCategoryInitContent');
        $result &= $this->registerHook('actionBeforeEverTagInitContent');
        $result &= $this->registerHook('actionBeforeEverBlogInitContent');
        $result &= $this->registerHook('actionBeforeEverBlogInit');
        $result &= $this->registerHook('displayBeforeEverPost');
        $result &= $this->registerHook('displayAfterEverPost');
        $result &= $this->registerHook('displayBeforeEverCategory');
        $result &= $this->registerHook('displayAfterEverCategory');
        $result &= $this->registerHook('displayBeforeEverTag');
        $result &= $this->registerHook('displayAfterEverTag');
        $result &= $this->registerHook('displayBeforeEverComment');
        $result &= $this->registerHook('displayAfterEverComment');
        $result &= $this->registerHook('displayBeforeEverLoop');
        $result &= $this->registerHook('displayAfterEverLoop');
        $result &= $this->registerHook('actionObjectProductDeleteAfter');
        $result &= $this->registerHook('actionObjectAuthorDeleteAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogTagDeleteAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogCategoryDeleteAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogPostDeleteAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogCommentDeleteAfter');
        $result &= $this->registerHook('actionObjectProductUpdateAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogAuthorUpdateAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogTagUpdateAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogCategoryUpdateAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogPostUpdateAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogCommentUpdateAfter');
        $result &= $this->registerHook('actionObjectProductAddAfter');
        $result &= $this->registerHook('actionObjectAuthorAddAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogTagAddAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogCategoryAddAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogPostAddAfter');
        $result &= $this->registerHook('actionObjectEverPsBlogCommentAddAfter');
        // Register prestashop hook
        $result &= $this->registerHook('actionObjectProductDeleteAfter');
        $result &= $this->registerHook('actionFrontControllerAfterInit');
        $result &= $this->registerHook('actionBeforeEverPostInitContent');
        $result &= $this->registerHook('actionBeforeEverCategoryInitContent');
        $result &= $this->registerHook('actionBeforeEverTagInitContent');
        $result &= $this->registerHook('actionBeforeEverBlogInitContent');
        $result &= $this->registerHook('actionBeforeEverBlogInit');
        $result &= $this->registerHook('actionAfterEverBlogInit');
        $result &= $this->registerHook('actionOutputHTMLBefore');
        return $result;
    }

    private function exportWordPressFile()
    {
        $all_posts = EverPsBlogPost::getPosts(
            (int)Context::getContext()->language->id,
            (int)Context::getContext()->shop->id,
            0,
            99999
        );
        $dom->encoding = 'utf-8';
        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xml_file_name = 'blog_'.Configuration::get('PS_SHOP_NAME').'.xml';
        // RSS node and his attributes
        $root = $dom->createElement('rss');
        $rss_version = new DOMAttr('version', '2.0');
        $root->setAttributeNode($rss_version);
        $xmlns_excerpt = new DOMAttr('xmlns:excerpt', 'http://wordpress.org/export/1.2/excerpt/');
        $root->setAttributeNode($xmlns_excerpt);
        $xmlns_content = new DOMAttr('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $root->setAttributeNode($xmlns_content);
        $xmlns_wfw = new DOMAttr('xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
        $root->setAttributeNode($xmlns_wfw);
        $xmlns_dc = new DOMAttr('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $root->setAttributeNode($xmlns_dc);
        $xmlns_wp = new DOMAttr('xmlns:wp', 'http://wordpress.org/export/1.2/');
        $root->setAttributeNode($xmlns_wp);
        foreach ($all_posts as $post) {
            # code...
        }
    }

    private function importWordPressFile($file)
    {
        $allow_iframes = Configuration::get('PS_ALLOW_HTML_IFRAME');
        if ((bool)$allow_iframes === false) {
            Configuration::updateValue('PS_ALLOW_HTML_IFRAME', true);
        }
        $result = true;
        $xml_str = Tools::file_get_contents($file['tmp_name']);
        $xml_str = str_replace(
            'content:encoded',
            'content',
            $xml_str
        );
        $xml_str = str_replace(
            'dc:creator',
            'creator',
            $xml_str
        );
        $xml_str = str_replace(
            'wp:post_date',
            'date_add',
            $xml_str
        );
        $xml_str = str_replace(
            'wp:post_name',
            'link_rewrite',
            $xml_str
        );
        $obj = new SimpleXMLElement($xml_str, LIBXML_NOCDATA);
        foreach ($obj->channel->item as $el) {
            // Post categories and post tags
            $post_categories = array();
            $post_tags = array();
            $parent_category = 1;
            foreach ($el->category as $wp_taxonomy) {
                if ($wp_taxonomy->attributes()['domain'] == 'category'
                    && (bool)Configuration::get('EVERBLOG_IMPORT_CATS') === true
                ) {
                    $category = EverPsBlogCategory::getCategoryByLinkRewrite(
                        (string)$wp_taxonomy['nicename']
                    );
                    if (!Validate::isLoadedObject($category)) {
                        $category = new EverPsBlogCategory();
                        foreach (Language::getLanguages(false) as $lang) {
                            $category->title[$lang['id_lang']] = (string)$wp_taxonomy;
                            $category->meta_title[$lang['id_lang']] = (string)$wp_taxonomy;
                            $category->link_rewrite[$lang['id_lang']] = (string)$wp_taxonomy['nicename'];
                        }
                        $category->id_parent_category = (int)$parent_category;
                        $category->id_shop = (int)Context::getContext()->shop->id;
                        $category->active = true;
                        $category->index = true;
                        $category->follow = true;
                        $category->sitemap = true;
                        $category->active = (bool)Configuration::get('EVERBLOG_ENABLE_CATS');
                        $result &= $category->save();
                        $post_categories[] = $category->id;
                    } else {
                        $post_categories[] = $category->id;
                    }
                } elseif ($wp_taxonomy->attributes()['domain'] == 'post_tag'
                    && (bool)Configuration::get('EVERBLOG_IMPORT_TAGS') === true
                ) {
                    $tag = EverPsBlogTag::getTagByLinkRewrite(
                        (string)$wp_taxonomy['nicename']
                    );
                    if (!Validate::isLoadedObject($tag)) {
                        $tag = new EverPsBlogTag();
                        foreach (Language::getLanguages(false) as $lang) {
                            $tag->title[$lang['id_lang']] = (string)$wp_taxonomy;
                            $tag->meta_title[$lang['id_lang']] = (string)$wp_taxonomy;
                            $tag->link_rewrite[$lang['id_lang']] = (string)$wp_taxonomy['nicename'];
                        }
                        $tag->id_shop = (int)Context::getContext()->shop->id;
                        $tag->active = true;
                        $tag->index = true;
                        $tag->follow = true;
                        $tag->sitemap = true;
                        $tag->active = (bool)Configuration::get('EVERBLOG_ENABLE_TAGS');
                        $result &= $tag->save();
                        $post_tags[] = $tag->id;
                    } else {
                        $post_tags[] = $tag->id;
                    }
                }
            }
            // Post author
            $author = EverPsBlogAuthor::getAuthorByNickhandle(
                (string)$el->creator
            );
            if (!Validate::isLoadedObject($author)
                && (bool)Configuration::get('EVERBLOG_IMPORT_AUTHORS') === true
            ) {
                $author = new EverPsBlogAuthor();
                $author->nickhandle = (string)$el->creator;
                foreach (Language::getLanguages(false) as $lang) {
                    $author->meta_title[$lang['id_lang']] = (string)$el->creator;
                    $author->link_rewrite[$lang['id_lang']] = EverPsBlogCleaner::convertToUrlRewrite(
                        (string)$el->creator
                    );
                }
                $author->id_shop = (int)Context::getContext()->shop->id;
                $author->active = true;
                $author->index = true;
                $author->follow = true;
                $author->sitemap = true;
                $author->active = (bool)Configuration::get('EVERBLOG_ENABLE_AUTHORS');
                $result &= $author->save();
            }
            // Post
            $post_link_rewrite = parse_url($el->link);
            $host = $post_link_rewrite['host'];
            $post_link_rewrite = str_replace('/', '', $post_link_rewrite['path']);
            $post = EverPsBlogPost::getPostByLinkRewrite(
                $post_link_rewrite
            );
            if (!Validate::isLoadedObject($post)) {
                // Copy images
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($el->content);
                $images = $dom->getElementsByTagName('img');
                foreach ($images as $item) {
                    $src = $item->getAttribute('src');
                    // Let's avoid 404 errors
                    $handle = curl_init($src);
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($handle);
                    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                    if ($httpCode != 200) {
                        curl_close($handle);
                        continue;
                    }
                    curl_close($handle);
                    // Copy img that are found and does not already exist
                    if (!file_exists(_PS_IMG_DIR_.'cms/'.utf8_decode(basename($src)))) {
                        copy(
                            $src,
                            _PS_IMG_DIR_.'cms/'.utf8_decode(basename($src))
                        );
                    }
                    // Check img attributes
                    $item->setAttribute(
                        'src',
                        $this->siteUrl.'cms/'.utf8_decode(basename($src))
                    );
                    $item->setAttribute(
                        'style',
                        'max-width:100%;'
                    );
                    if (!$item->getAttribute('alt') || empty($item->getAttribute('alt'))) {
                        $item->setAttribute(
                            'alt',
                            utf8_decode(basename($src))
                        );
                    }
                }
                // Clean anchors, but internal links wont be available
                $anchors = $dom->getElementsByTagName('a');
                foreach ($anchors as $item) {
                    $href = $item->getAttribute('href');
                    $href_array = parse_url($href);
                    if (isset($href_array['host'])) {
                        $host = $href_array['host'];
                        $item->setAttribute(
                            'src',
                            str_replace($host, $this->siteUrl, $href)
                        );
                    }
                }
                $dom->saveHTML();
                $post = new EverPsBlogPost();
                $post_content = preg_replace('/<!--(.|\s)*?-->/', '', $el->content);
                $post_content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $post_content);
                $post_content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $post_content);
                // Multilingual fields
                foreach (Language::getLanguages(false) as $lang) {
                    $post->title[$lang['id_lang']] = (string)$el->title;
                    $post->meta_title[$lang['id_lang']] = (string)$el->title;
                    $post->meta_description[$lang['id_lang']] = Tools::substr(
                        strip_tags($post_content),
                        0,
                        160
                    );
                    $post->link_rewrite[$lang['id_lang']] = $post_link_rewrite;
                    $post->content = $post_content;
                    if (!Validate::isCleanHtml($post_content, true)) {
                        continue 2;
                    }
                }
                $post->id_shop = (int)Context::getContext()->shop->id;
                $post->active = true;
                $post->index = true;
                $post->follow = true;
                $post->sitemap = true;
                $post->active = true;
                $post->date_add = $el->date_add;
                $post->date_upd = $post->date_add;
                $post->post_status = Configuration::get('EVERBLOG_IMPORT_POST_STATE');
                if (Validate::isLoadedObject($author)) {
                    $post->id_author = $author->id;
                }
                if (!empty($post_categories)) {
                    $post->id_default_category = $post_categories[0];
                    $post->post_categories = json_encode($post_categories);
                }
                if (!empty($post_tags)) {
                    $post->post_tags = json_encode($post_tags);
                }
                $result &= $post->save();
            }
        }
        // Reset iframes
        if ((bool)$allow_iframes === false) {
            Configuration::updateValue('PS_ALLOW_HTML_IFRAME', false);
        }
        if ((bool)$result === true) {
            $this->generateBlogSitemap();
            $this->postSuccess[] = $this->l('WordPress posts have been imported');
        } else {
            $this->postErrors[] = $this->l('An error has occured while importing WordPress file');
        }
    }

    public function checkLatestEverModuleVersion($module, $version)
    {
        $upgrade_link = 'https://upgrade.team-ever.com/upgrade.php?module='
        .$module
        .'&version='
        .$version;
        $handle = curl_init($upgrade_link);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        if ($httpCode != 200) {
            return false;
        }
        $module_version = Tools::file_get_contents(
            $upgrade_link
        );
        if ($module_version && $module_version > $version) {
            return true;
        }
        return false;
    }
}
