<?php
/**
 * 2019-2025 Team Ever
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
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogCleaner.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogImage.php';

class AdminEverPsBlogCategoryController extends ModuleAdminController
{
    private $html;

    public function __construct()
    {
        $this->name = 'AdminEverPsBlogCategoryController';
        $this->bootstrap = true;
        $this->display = $this->l('Ever Blog Categories');
        $this->table = 'ever_blog_category';
        $this->className = 'EverPsBlogCategory';
        $this->module_name = 'everpsblog';
        $this->shop_url = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $this->img_url = $this->shop_url . 'modules/' . $this->module_name . '/views/img/';
        $this->context = Context::getContext();
        $this->identifier = 'id_ever_category';
        $this->_orderBy = $this->identifier;
        $this->_orderWay = 'DESC';
        $this->fields_list = [
            'id_ever_category' => [
                'title' => $this->l('Category ID'),
                'align' => 'left',
                'width' => 25,
            ],
            'featured_img' => [
                'title' => $this->l('Featured image'),
                'align' => 'center',
                'width' => 25,
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'image' => 'category',
            ],
            'title' => [
                'title' => $this->l('Category title'),
                'align' => 'left',
            ],
            'active' => [
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'statusactive',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
            'indexable' => [
                'title' => $this->l('Index'),
                'type' => 'bool',
                'active' => 'statusindex',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
            'follow' => [
                'title' => $this->l('Follow'),
                'type' => 'bool',
                'active' => 'statusfollow',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
            'sitemap' => [
                'title' => $this->l('Sitemap'),
                'type' => 'bool',
                'active' => 'statussitemap',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
            'count' => [
                'title' => $this->l('Views count'),
                'align' => 'left',
            ],
            'date_add' => [
                'title' => $this->l('Date add'),
                'align' => 'left',
            ],
            'date_upd' => [
                'title' => $this->l('Date upd'),
                'align' => 'left',
            ],
        ];
        $this->colorOnBackground = true;
        $this->unclassedCategory = (int) Configuration::get('EVERBLOG_UNCLASSED_ID');
        $this->_select = 'l.title,
        CONCAT("' . $this->img_url . '",ai.image_link) AS featured_img';

        $this->_join =
            'LEFT JOIN `' . _DB_PREFIX_ . $this->table . '_lang` l
                ON (
                    l.`' . $this->identifier . '` = a.`' . $this->identifier . '`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_image` ai
                ON (
                    ai.`id_ever_image` = a.`' . $this->identifier . '`
                    AND ai.`image_type` = "category"
                )';
        $this->_where = 'AND a.id_shop = ' . (int) $this->context->shop->id
        .' AND a.is_root_category != 1'
        .' AND a.id_ever_category != ' . $this->unclassedCategory
        .' AND l.id_lang = ' . (int) $this->context->language->id;
        $moduleConfUrl  = 'index.php?controller=AdminModules&configure=everpsblog&token=';
        $moduleConfUrl .= Tools::getAdminTokenLite('AdminModules');
        $postUrl  = 'index.php?controller=AdminEverPsBlogPost&token=';
        $postUrl .= Tools::getAdminTokenLite('AdminEverPsBlogPost');
        $authorUrl  = 'index.php?controller=AdminEverPsBlogAuthor&token=';
        $authorUrl .= Tools::getAdminTokenLite('AdminEverPsBlogAuthor');
        $categoryUrl  = 'index.php?controller=AdminEverPsBlogCategory&token=';
        $categoryUrl .= Tools::getAdminTokenLite('AdminEverPsBlogCategory');
        $tagUrl  = 'index.php?controller=AdminEverPsBlogTag&token=';
        $tagUrl .= Tools::getAdminTokenLite('AdminEverPsBlogTag');
        $commentUrl  = 'index.php?controller=AdminEverPsBlogComment&token=';
        $commentUrl .= Tools::getAdminTokenLite('AdminEverPsBlogComment');
        $blogUrl = Context::getContext()->link->getModuleLink(
            'everpsblog',
            'blog',
            [],
            true
        );
        $ever_blog_token = Tools::encrypt('everpsblog/cron');
        $emptytrash = $this->context->link->getModuleLink(
            $this->module_name,
            'emptytrash',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id,
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $pending = $this->context->link->getModuleLink(
            $this->module_name,
            'pending',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id,
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $planned = $this->context->link->getModuleLink(
            $this->module_name,
            'planned',
            [
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id,
            ],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $this->context->smarty->assign([
            'image_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__.'/modules/everpsblog/views/img/',
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
        parent::__construct();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            'Modules.Everpsblog.Admineverpsblogcategorycontroller'
        );
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new'] = [
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new element'),
            'icon' => 'process-icon-new',
        ];
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('ViewCategory');
        $this->toolbar_title = $this->l('Categories settings');
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?'),
            ],
        ];
        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $this->processBulkDelete();
        }
        if (Tools::isSubmit('submitBulkdisableSelection' . $this->table)) {
            $this->processBulkDisable();
        }
        if (Tools::isSubmit('submitBulkenableSelection' . $this->table)) {
            $this->processBulkEnable();
        }
        $lists = parent::renderList();
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
            . '/' . $this->module->name . '/views/templates/admin/headerController.tpl'
        );
        $blog_instance = Module::getInstanceByName($this->module_name);
        if ($blog_instance->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_
                . '/'
                . $this->module_name
                . '/views/templates/admin/upgrade.tpl'
            );
        }
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
        );
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
            . '/' . $this->module->name . '/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP
            && Shop::isFeatureActive()
        ) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new element.');
            return false;
        }
        $category_id = (int) Tools::getValue($this->identifier);
        $obj = new $this->className(
            (int) Tools::getValue($this->identifier)
        );
        $categories = EverPsBlogCategory::getAllCategories(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            1
        );
        $fileUrl = EverPsBlogImage::getBlogImageUrl(
            (int) $category_id,
            (int) $this->context->shop->id,
            'category'
        );
        $groups = Group::getGroups(
            (int) $this->context->language->id
        );
        $categoryImg = '<image src="' . $fileUrl . '" style="max-width:150px;"/>';

        // Building the Add/Edit form
        $fields_form = [];
        $fields_form[] = [
            'form' => [
                'tinymce' => true,
                'description' => $this->l('Please fill this form to set category.'),
                'submit' => [
                    'name' => 'save',
                    'title' => $this->l('Save'),
                    'class' => 'button pull-right',
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminEverPsBlogCategory', true),
                        'title' => $this->l('Cancel'),
                        'icon' => 'process-icon-cancel',
                    ],
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => $this->identifier,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Parent category'),
                        'desc' => $this->l('Please choose parent category'),
                        'hint' => $this->l('At least parent must be root category'),
                        'name' => 'id_parent_category',
                        'identifier' => 'name',
                        'options' => [
                            'query' => $categories,
                            'id' => $this->identifier,
                            'name' => 'title',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'multiple' => true,
                        'label' => $this->l('Allowed groups'),
                        'desc' => $this->l('Please select allowed groups for viewing'),
                        'hint' => $this->l('Leave empty for no use'),
                        'name' => 'allowed_groups[]',
                        'identifier' => 'name',
                        'options' => [
                            'query' => $groups,
                            'id' => 'id_group',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Category meta title'),
                        'desc' => $this->l('More than 45 characters, less than 65'),
                        'hint' => $this->l('Required for better SEO'),
                        'maxchar' => 65,
                        'required' => true,
                        'name' => 'meta_title',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Category meta description'),
                        'desc' => $this->l('More than 90 characters, less than 165'),
                        'hint' => $this->l('Required for better SEO'),
                        'maxchar' => 165,
                        'required' => true,
                        'name' => 'meta_description',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Category link rewrite'),
                        'desc' => $this->l('For rewrite rules'),
                        'hint' => $this->l('Will set category base URL'),
                        'required' => true,
                        'name' => 'link_rewrite',
                        'class' => 'copy2friendlyUrl',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Category title'),
                        'desc' => $this->l('Please choose category title'),
                        'hint' => $this->l('Will shown as title 1 on top of category'),
                        'required' => true,
                        'name' => 'title',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Category content'),
                        'desc' => $this->l('Please set category content here'),
                        'hint' => $this->l('Category content will be shown on top of page'),
                        'required' => true,
                        'name' => 'content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Category bottom content'),
                        'desc' => $this->l('Please set category bottom content here'),
                        'hint' => $this->l('Category content will be shown on bottom of page'),
                        'required' => false,
                        'name' => 'bottom_content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Category image'),
                        'desc' => $this->l('Featured category image'),
                        'hint' => $this->l('If empty, your shop logo will be used'),
                        'name' => 'category_image',
                        'display_image' => true,
                        'image' => $categoryImg,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('SEO index category ?'),
                        'desc' => $this->l('Set yes to index, no to noindex'),
                        'hint' => $this->l('This will impact your SEO'),
                        'name' => 'indexable',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('SEO follow category ?'),
                        'desc' => $this->l('Set yes to follow, no to nofollow'),
                        'hint' => $this->l('Do you want search engines to follow links on this category ?'),
                        'name' => 'follow',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('SEO sitemap category ?'),
                        'desc' => $this->l('Set yes to sitemap, no to nositemap'),
                        'hint' => $this->l('Please generate sitemaps after changing this rule'),
                        'name' => 'sitemap',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Activate category ?'),
                        'desc' => $this->l('Set yes to activate'),
                        'hint' => $this->l('Else this category won\'t be shown, but linked posts will'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->toolbar_scroll = true;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->token = Tools::getValue('token');
        $helper->submit_action = 'save';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues($obj),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int) Context::getContext()->language->id,
        ];
        $helper->currentIndex = AdminController::$currentIndex;
        return $helper->generateForm($fields_form);
    }

    protected function getConfigFormValues($obj)
    {
        $formValues = [];
        if (Validate::isLoadedObject($obj)) {
            if (is_array($obj->allowed_groups)) {
                $allowedGroups = '';
            } else {
                $allowedGroups = json_decode($obj->allowed_groups);
            }
            $formValues[] = [
                $this->identifier => (!empty(Tools::getValue($this->identifier)))
                ? Tools::getValue($this->identifier)
                : $obj->id,
                'title' => (!empty(Tools::getValue('title')))
                ? Tools::getValue('title')
                : $obj->title,
                'meta_title' => (!empty(Tools::getValue('meta_title')))
                ? Tools::getValue('meta_title')
                : $obj->meta_title,
                'meta_description' => (!empty(Tools::getValue('meta_description')))
                ? Tools::getValue('meta_description')
                : $obj->meta_description,
                'link_rewrite' => (!empty(Tools::getValue('link_rewrite')))
                ? Tools::getValue('link_rewrite')
                : $obj->link_rewrite,
                'content' => (!empty(Tools::getValue('content')))
                ? Tools::getValue('content')
                : $obj->content,
                'bottom_content' => (!empty(Tools::getValue('bottom_content')))
                ? Tools::getValue('bottom_content')
                : $obj->bottom_content,
                'date_add' => (!empty(Tools::getValue('date_add')))
                ? Tools::getValue('date_add')
                : $obj->date_add,
                'date_upd' => (!empty(Tools::getValue('date_upd')))
                ? Tools::getValue('date_upd')
                : $obj->date_upd,
                'id_parent_category' => (!empty(Tools::getValue('id_parent_category')))
                ? Tools::getValue('id_parent_category')
                : $obj->id_parent_category,
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : $obj->active,
                'indexable' => (!empty(Tools::getValue('indexable')))
                ? Tools::getValue('indexable')
                : $obj->indexable,
                'follow' => (!empty(Tools::getValue('follow')))
                ? Tools::getValue('follow')
                : $obj->follow,
                'allowed_groups[]' => (!empty(Tools::getValue('allowed_groups')))
                ? Tools::getValue('allowed_groups')
                : $allowedGroups,
                'sitemap' => (!empty(Tools::getValue('sitemap')))
                ? Tools::getValue('sitemap')
                : $obj->sitemap,
                'category_products' => (!empty(Tools::getValue('category_products')))
                ? Tools::getValue('category_products')
                : json_decode($obj->category_products),
                'is_root_category' => (!empty(Tools::getValue('is_root_category')))
                ? Tools::getValue('is_root_category')
                : $obj->is_root_category,
                'count' => (!empty(Tools::getValue('count')))
                ? Tools::getValue('count')
                : $obj->count,
            ];
        } else {
            $content = [];
            $bottomContent = [];
            foreach (Language::getLanguages(false) as $lang) {
                $content[$lang['id_lang']] = '';
                $bottomContent[$lang['id_lang']] = '';
            }
            $formValues[] = [
                $this->identifier => (!empty(Tools::getValue($this->identifier)))
                ? Tools::getValue($this->identifier)
                : '',
                'title' => (!empty(Tools::getValue('title')))
                ? Tools::getValue('title')
                : '',
                'meta_title' => (!empty(Tools::getValue('meta_title')))
                ? Tools::getValue('meta_title')
                : '',
                'meta_description' => (!empty(Tools::getValue('meta_description')))
                ? Tools::getValue('meta_description')
                : '',
                'link_rewrite' => (!empty(Tools::getValue('link_rewrite')))
                ? Tools::getValue('link_rewrite')
                : '',
                'content' => (!empty(Tools::getValue('content')))
                ? Tools::getValue('content')
                : $content,
                'bottom_content' => (!empty(Tools::getValue('bottom_content')))
                ? Tools::getValue('bottom_content')
                : $bottomContent,
                'date_add' => (!empty(Tools::getValue('date_add')))
                ? Tools::getValue('date_add')
                : '',
                'date_upd' => (!empty(Tools::getValue('date_upd')))
                ? Tools::getValue('date_upd')
                : '',
                'id_parent_category' => (!empty(Tools::getValue('id_parent_category')))
                ? Tools::getValue('id_parent_category')
                : '',
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : '',
                'indexable' => (!empty(Tools::getValue('indexable')))
                ? Tools::getValue('indexable')
                : '',
                'follow' => (!empty(Tools::getValue('follow')))
                ? Tools::getValue('follow')
                : '',
                'allowed_groups[]' => (!empty(Tools::getValue('allowed_groups')))
                ? Tools::getValue('allowed_groups')
                : '',
                'sitemap' => (!empty(Tools::getValue('sitemap')))
                ? Tools::getValue('sitemap')
                : '',
                'category_products' => (!empty(Tools::getValue('category_products')))
                ? Tools::getValue('category_products')
                : '',
                'is_root_category' => (!empty(Tools::getValue('is_root_category')))
                ? Tools::getValue('is_root_category')
                : '',
                'count' => (!empty(Tools::getValue('count')))
                ? Tools::getValue('count')
                : '',
            ];
        }
        $values = call_user_func_array('array_merge', $formValues);
        return $values;
    }

    public function postProcess()
    {
        if (Tools::getIsset('statusactive' . $this->table)) {
            $everObj = new EverPsBlogCategory(
                (int) Tools::getValue($this->identifier)
            );
            (bool) $everObj->active = !(bool) $everObj->active;
            $everObj->save();
        }
        if (Tools::getIsset('statusindex' . $this->table)) {
            $everObj = new EverPsBlogCategory(
                (int) Tools::getValue($this->identifier)
            );
            (bool) $everObj->indexable = !(bool) $everObj->indexable;
            $everObj->save();
        }
        if (Tools::getIsset('statusfollow' . $this->table)) {
            $everObj = new EverPsBlogCategory(
                (int) Tools::getValue($this->identifier)
            );
            (bool) $everObj->follow = !(bool) $everObj->follow;
            $everObj->save();
        }
        if (Tools::getIsset('statussitemap' . $this->table)) {
            $everObj = new EverPsBlogCategory(
                (int) Tools::getValue($this->identifier)
            );
            (bool) $everObj->sitemap = !(bool) $everObj->sitemap;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue($this->identifier)) {
                $category = new EverPsBlogCategory();
            } else {
                $category = new EverPsBlogCategory(
                    (int) Tools::getValue($this->identifier)
                );
            }
            if (!Tools::getValue('id_parent_category')
                || !Validate::isInt(Tools::getValue('id_parent_category'))
            ) {
                $category->id_parent_category = $this->unclassedCategory;
            } else {
                $category->id_parent_category = Tools::getValue('id_parent_category');
            }
            if (Tools::getValue('allowed_groups')
                && !Validate::isArrayWithIds(Tools::getValue('allowed_groups'))
            ) {
                $this->errors[] = $this->l('Allowed groups is not valid');
            } else {
                $category->allowed_groups = json_encode(Tools::getValue('allowed_groups'));
            }
            if (Tools::getValue('indexable')
                && !Validate::isBool(Tools::getValue('indexable'))
            ) {
                $this->errors[] = $this->l('Index is not valid');
            } else {
                $category->indexable = Tools::getValue('indexable');
            }
            if (Tools::getValue('follow')
                && !Validate::isBool(Tools::getValue('follow'))
            ) {
                $this->errors[] = $this->l('Follow is not valid');
            } else {
                $category->follow = Tools::getValue('follow');
            }
            if (Tools::getValue('sitemap')
                && !Validate::isBool(Tools::getValue('sitemap'))
            ) {
                $this->errors[] = $this->l('Sitemap is not valid');
            } else {
                $category->sitemap = Tools::getValue('sitemap');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                $this->errors[] = $this->l('Active is not valid');
            } else {
                $category->active = Tools::getValue('active');
            }
            $category->id_shop = (int) $this->context->shop->id;
            // Multilingual fields
            if (!Tools::getValue('id_ever_category')) {
                $category->date_add = date('Y-m-d H:i:s');
            }
            $category->date_upd = date('Y-m-d H:i:s');
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('title_' . $lang['id_lang'])
                    && !Validate::isString(Tools::getValue('title_' . $lang['id_lang']))
                ) {
                    $this->errors[] = $this->l(
                        'Title is not valid for lang '
                    ) . $lang['id_lang'];
                } else {
                    $category->title[$lang['id_lang']] = Tools::getValue('title_' . $lang['id_lang']);
                }
                if (Tools::getValue('content_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('content_' . $lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l(
                        'Content is not valid for lang '
                    ) . $lang['id_lang'];
                } else {
                    $category->content[$lang['id_lang']] = Tools::getValue('content_' . $lang['id_lang']);
                }
                if (Tools::getValue('bottom_content_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('bottom_content_' . $lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l(
                        'Bottom content is not valid for lang '
                    ) . $lang['id_lang'];
                } else {
                    $category->bottom_content[$lang['id_lang']] = Tools::getValue('bottom_content_' . $lang['id_lang']);
                }
                if (Tools::getValue('meta_title_' . $lang['id_lang'])
                    && !Validate::isString(Tools::getValue('meta_title_' . $lang['id_lang']))
                ) {
                    $this->errors[] = $this->l(
                        'Meta title is not valid for lang '
                    ) . $lang['id_lang'];
                } else {
                    $category->meta_title[$lang['id_lang']] = Tools::getValue('meta_title_' . $lang['id_lang']);
                }
                if (Tools::getValue('meta_description_'.$lang['id_lang'])
                    && !Validate::isString(Tools::getValue('meta_description_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l(
                        'Meta description is not valid for lang '
                    ) . $lang['id_lang'];
                } else {
                    $category->meta_description[$lang['id_lang']] = Tools::getValue(
                        'meta_description_' . $lang['id_lang']
                    );
                }
                if (Tools::getValue('link_rewrite_' . $lang['id_lang'])
                    && !Validate::isLinkRewrite(Tools::getValue('link_rewrite_' . $lang['id_lang']))
                ) {
                    $category->link_rewrite[$lang['id_lang']] = Tools::str2url(
                        Tools::getValue('title_' . $lang['id_lang'])
                    );
                } else {
                    $category->link_rewrite[$lang['id_lang']] = Tools::str2url(
                        Tools::getValue('link_rewrite_' . $lang['id_lang'])
                    );
                }
            }
            if (!count($this->errors)) {
                try {
                    $category->save();
                    $category_img_link = 'img/category/'
                    . (int) $category->id
                    . '.jpg';
                    $ps_categories_destination = _PS_IMG_DIR_
                    . 'category/'
                    . (int) $category->id
                    . '.jpg';
                    if (!file_exists(_PS_IMG_DIR_ . 'category')) {
                        mkdir(_PS_IMG_DIR_ . 'category', 0755, true);
                    }
                    /* upload the image */
                    if (isset($_FILES['category_image'])
                        && isset($_FILES['category_image']['tmp_name'])
                        && !empty($_FILES['category_image']['tmp_name'])
                    ) {
                        Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
                        if (file_exists($ps_categories_destination)) {
                            unlink($ps_categories_destination);
                        }
                        if ($error = ImageManager::validateUpload($_FILES['category_image'])) {
                            $this->errors .= $error;
                        } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                            || !move_uploaded_file($_FILES['category_image']['tmp_name'], $tmp_name)
                        ) {
                            return false;
                        } elseif (!ImageManager::resize($tmp_name, $ps_categories_destination)) {
                            $this->errors .= $this->l(
                                'An error occurred while attempting to upload the image.'
                            );
                        }
                        if (isset($tmp_name)) {
                            unlink($tmp_name);
                        }
                        $featured_image = EverPsBlogImage::getBlogImage(
                            (int) $category->id,
                            (int) Context::getContext()->shop->id,
                            'category'
                        );
                        if (!$featured_image) {
                            $featured_image = new EverPsBlogImage();
                        }
                        $featured_image->id_element = (int) $category->id;
                        $featured_image->image_type = 'category';
                        $featured_image->image_link = $category_img_link;
                        $featured_image->id_shop = (int) Context::getContext()->shop->id;
                        return $featured_image->save();
                    }
                } catch (Exception $e) {
                    PrestaShopLogger::addLog($this->module->name . ' : admin category : ' . $e->getMessage());
                }
            } else {
                $this->display = 'edit';
            }
        }
        Tools::clearCache();
        parent::postProcess();
    }

    public function displayViewCategoryLink($token, $id_ever_category)
    {
        if (!$token) {
            return;
        }
        $category = new EverPsBlogCategory($id_ever_category);
        $link = new Link();
        $id_lang = (int) Context::getContext()->language->id;
        $seeUrl = $link->getModuleLink(
            $this->module->name,
            'category',
            [
                $this->identifier => $category->id,
                'link_rewrite' => $category->link_rewrite[$id_lang],
            ]
        );
        $this->context->smarty->assign([
            'href' => $seeUrl,
            'confirm' => null,
            'action' => $this->l('View category'),
        ]);
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/helpers/lists/list_action_view_obj.tpl'
        );
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new EverPsBlogCategory((int) $idEverObj);
            if ($everObj->active) {
                $everObj->active = false;
            }
            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t disable the current object');
            }
        }
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new EverPsBlogCategory((int) $idEverObj);
            if (!$everObj->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkEnable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new EverPsBlogCategory((int) $idEverObj);
            if (!$everObj->active) {
                $everObj->active = true;
            }
            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t enable the current object');
            }
        }
    }

    protected function displayError($message, $description = false)
    {
        /**
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);
        return $this->setTemplate('error.tpl');
    }
}
