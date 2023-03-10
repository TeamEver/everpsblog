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
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->bootstrap = true;
        $this->display = $this->l('Ever Blog Categories');
        $this->table = 'ever_blog_category';
        $this->className = 'EverPsBlogCategory';
        $this->module_name = 'everpsblog';
        $this->shop_url = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $this->img_url = $this->shop_url.'modules/'.$this->module_name.'/views/img/';
        $this->context = Context::getContext();
        $this->identifier = "id_ever_category";
        $this->_orderBy = 'id_ever_category';
        $this->_orderWay = 'DESC';
        $this->fields_list = array(
            'id_ever_category' => array(
                'title' => $this->l('Category ID'),
                'align' => 'left',
                'width' => 25
            ),
            'featured_img' => array(
                'title' => $this->l('Featured image'),
                'align' => 'center',
                'width' => 25,
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'image' => 'category',
            ),
            'title' => array(
                'title' => $this->l('Category title'),
                'align' => 'left'
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'statusactive',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
            'index' => array(
                'title' => $this->l('Index'),
                'type' => 'bool',
                'active' => 'statusindex',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
            'follow' => array(
                'title' => $this->l('Follow'),
                'type' => 'bool',
                'active' => 'statusfollow',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
            'sitemap' => array(
                'title' => $this->l('Sitemap'),
                'type' => 'bool',
                'active' => 'statussitemap',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
            'count' => array(
                'title' => $this->l('Views count'),
                'align' => 'left'
            ),
        );

        $this->colorOnBackground = true;
        $this->_select = 'l.title,
        CONCAT("'.$this->img_url.'",ai.image_link) AS featured_img';

        $this->_join =
            'LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_lang` l
                ON (
                    l.`id_ever_category` = a.`id_ever_category`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_image` ai
                ON (
                    ai.`id_ever_image` = a.`id_ever_category`
                    AND ai.`image_type` = "category"
                )';
        $this->_where = 'AND a.id_shop = '.(int) $this->context->shop->id
        .' AND a.is_root_category != 1'
        .' AND l.id_lang = '.(int) $this->context->language->id;
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
            array(),
            true
        );
        $ever_blog_token = Tools::encrypt('everpsblog/cron');
        $emptytrash = $this->context->link->getModuleLink(
            $this->module_name,
            'emptytrash',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id
            ),
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $pending = $this->context->link->getModuleLink(
            $this->module_name,
            'pending',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id
            ),
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $planned = $this->context->link->getModuleLink(
            $this->module_name,
            'planned',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int) $this->context->shop->id
            ),
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $this->context->smarty->assign(array(
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
        ));

        parent::__construct();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsblog.Admineverpsblogcategorycontroller'
            );
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new element'),
            'icon' => 'process-icon-new'
        );
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('ViewCategory');
        $this->toolbar_title = $this->l('Categories settings');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?')
            ),
        );

        if (Tools::isSubmit('submitBulkdelete'.$this->table)) {
            $this->processBulkDelete();
        }
        if (Tools::isSubmit('submitBulkdisableSelection'.$this->table)) {
            $this->processBulkDisable();
        }
        if (Tools::isSubmit('submitBulkenableSelection'.$this->table)) {
            $this->processBulkEnable();
        }

        $lists = parent::renderList();

        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
            .'/everpsblog/views/templates/admin/headerController.tpl'
        );
        $blog_instance = Module::getInstanceByName($this->module_name);
        if ($blog_instance->checkLatestEverModuleVersion($this->module_name, $blog_instance->version)) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_
                .'/'
                .$this->module_name
                .'/views/templates/admin/upgrade.tpl'
            );
        }
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
            .'/everpsblog/views/templates/admin/shortcodes.tpl'
        );
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
            .'/everpsblog/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new element.');
            return false;
        }
        
        $category_id = (int)Tools::getValue('id_ever_category');
        $categories = EverPsBlogCategory::getAllCategories(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            1
        );
        $file_url = EverPsBlogImage::getBlogImageUrl(
            (int) $category_id,
            (int) $this->context->shop->id,
            'category'
        );
        $categoryImg = '<image src="'.(string) $file_url.'" style="max-width:150px;"/>';

        // Building the Add/Edit form
        $this->fields_form = array(
            'tinymce' => true,
            'description' => $this->l('Please fill this form to set category.'),
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Save'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Parent category'),
                    'desc' => $this->l('Please choose parent category'),
                    'hint' => $this->l('At least parent must be root category'),
                    'name' => 'id_parent_category',
                    'identifier' => 'name',
                    'options' => array(
                        'query' => $categories,
                        'id' => 'id_ever_category',
                        'name' => 'title',
                    ),
                ),
                array(
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
                    'rows' => 30
                ),
                array(
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
                    'rows' => 30
                ),
                array(
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
                    'rows' => 30
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Category title'),
                    'desc' => $this->l('Please choose category title'),
                    'hint' => $this->l('Will shown as title 1 on top of category'),
                    'required' => true,
                    'name' => 'title',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Category content'),
                    'desc' => $this->l('Please set category content here'),
                    'hint' => $this->l('Category content will be shown on top of page'),
                    'required' => true,
                    'name' => 'content',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Category bottom content'),
                    'desc' => $this->l('Please set category bottom content here'),
                    'hint' => $this->l('Category content will be shown on bottom of page'),
                    'required' => false,
                    'name' => 'bottom_content',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Category image'),
                    'desc' => $this->l('Featured category image'),
                    'hint' => $this->l('If empty, your shop logo will be used'),
                    'name' => 'category_image',
                    'display_image' => true,
                    'image' => $categoryImg
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('SEO index category ?'),
                    'desc' => $this->l('Set yes to index, no to noindex'),
                    'hint' => $this->l('This will impact your SEO'),
                    'name' => 'index',
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
                    'label' => $this->l('SEO follow category ?'),
                    'desc' => $this->l('Set yes to follow, no to nofollow'),
                    'hint' => $this->l('Do you want search engines to follow links on this category ?'),
                    'name' => 'follow',
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
                        'label' => $this->l('SEO sitemap category ?'),
                        'desc' => $this->l('Set yes to sitemap, no to nositemap'),
                        'hint' => $this->l('Please generate sitemaps after changing this rule'),
                        'name' => 'sitemap',
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
                    'label' => $this->l('Activate category ?'),
                    'desc' => $this->l('Set yes to activate'),
                    'hint' => $this->l('Else this category won\'t be shown, but linked posts will'),
                    'name' => 'active',
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
            )
        );
        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::getIsset('statusactiveever_blog_category')) {
            $everObj = new EverPsBlogCategory(
                (int)Tools::getValue('id_ever_category')
            );
            (int) $everObj->active = !(int) $everObj->active;
            $everObj->save();
        }
        if (Tools::getIsset('statusindexever_blog_category')) {
            $everObj = new EverPsBlogCategory(
                (int)Tools::getValue('id_ever_category')
            );
            (int) $everObj->index = !(int) $everObj->index;
            $everObj->save();
        }
        if (Tools::getIsset('statusfollowever_blog_category')) {
            $everObj = new EverPsBlogCategory(
                (int)Tools::getValue('id_ever_category')
            );
            (int) $everObj->follow = !(int) $everObj->follow;
            $everObj->save();
        }
        if (Tools::getIsset('statussitemapever_blog_category')) {
            $everObj = new EverPsBlogCategory(
                (int)Tools::getValue('id_ever_category')
            );
            (int) $everObj->sitemap = !(int) $everObj->sitemap;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue('id_ever_category')) {
                $category = new EverPsBlogCategory();
            } else {
                $category = new EverPsBlogCategory(
                    (int)Tools::getValue('id_ever_category')
                );
            }
            if (!Tools::getValue('id_parent_category')
                || !Validate::isInt(Tools::getValue('id_parent_category'))
            ) {
                $this->errors[] = $this->l('Parent category is not valid');
            } else {
                $category->id_parent_category = Tools::getValue('id_parent_category');
            }
            if (Tools::getValue('index')
                && !Validate::isBool(Tools::getValue('index'))
            ) {
                $this->errors[] = $this->l('Index is not valid');
            } else {
                $category->index = Tools::getValue('index');
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
            if (!(int)Tools::getValue('id_ever_category')) {
                $category->date_add = date('Y-m-d H:i:s');
            }
            $category->date_upd = date('Y-m-d H:i:s');
            // Multilingual fields
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('title_'.$lang['id_lang'])
                    && !Validate::isString(Tools::getValue('title_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l(
                        'Title is not valid for lang '
                    ).$lang['id_lang'];
                } else {
                    $category->title[$lang['id_lang']] = Tools::getValue('title_'.$lang['id_lang']);
                }
                if (Tools::getValue('content_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('content_'.$lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l(
                        'Content is not valid for lang '
                    ).$lang['id_lang'];
                } else {
                    $category->content[$lang['id_lang']] = Tools::getValue('content_'.$lang['id_lang']);
                }
                if (Tools::getValue('bottom_content_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('bottom_content_'.$lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l(
                        'Bottom content is not valid for lang '
                    ).$lang['id_lang'];
                } else {
                    $category->bottom_content[$lang['id_lang']] = Tools::getValue('bottom_content_'.$lang['id_lang']);
                }
                if (Tools::getValue('meta_title_'.$lang['id_lang'])
                    && !Validate::isString(Tools::getValue('meta_title_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l(
                        'Meta title is not valid for lang '
                    ).$lang['id_lang'];
                } else {
                    $category->meta_title[$lang['id_lang']] = Tools::getValue('meta_title_'.$lang['id_lang']);
                }
                if (Tools::getValue('meta_description_'.$lang['id_lang'])
                    && !Validate::isString(Tools::getValue('meta_description_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l(
                        'Meta description is not valid for lang '
                    ).$lang['id_lang'];
                } else {
                    $category->meta_description[$lang['id_lang']] = Tools::getValue(
                        'meta_description_'.$lang['id_lang']
                    );
                }
                if (Tools::getValue('link_rewrite_'.$lang['id_lang'])
                    && !Validate::isLinkRewrite(Tools::getValue('link_rewrite_'.$lang['id_lang']))
                ) {
                    $category->link_rewrite[$lang['id_lang']] = EverPsBlogCleaner::convertToUrlRewrite(
                        Tools::getValue('title_'.$lang['id_lang'])
                    );
                } else {
                    $category->link_rewrite[$lang['id_lang']] = Tools::getValue('link_rewrite_'.$lang['id_lang']);
                }
            }
            if (!count($this->errors)) {
                $category->save();
                $category_img_link = 'img/category/'
                .(int) $category->id
                .'.jpg';
                $ps_categories_destination = _PS_IMG_DIR_
                .'category/'
                .(int) $category->id
                .'.jpg';
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
        $see_url = $link->getModuleLink(
            'everpsblog',
            'category',
            array(
                'id_ever_category' => $category->id,
                'link_rewrite' => $category->link_rewrite[$id_lang]
            )
        );

        $this->context->smarty->assign(array(
            'href' => $see_url,
            'confirm' => null,
            'action' => $this->l('View category')
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'everpsblog/views/templates/admin/helpers/lists/list_action_view_obj.tpl'
        );
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
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
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everObj = new EverPsBlogCategory((int) $idEverObj);
            
            if (!$everObj->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkEnable()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
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
