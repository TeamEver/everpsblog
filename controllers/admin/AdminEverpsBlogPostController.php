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
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogAuthor.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogTaxonomy.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogImage.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogCleaner.php';

class AdminEverPsBlogPostController extends ModuleAdminController
{
    private $html;
    public $name;

    public function __construct()
    {
        $this->name = 'AdminEverPsBlogPostController';
        $this->bootstrap = true;
        $this->display = $this->l('Ever Blog Posts');
        $this->table = 'ever_blog_post';
        $this->className = 'EverPsBlogPost';
        $this->shop_url = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $this->img_url = $this->shop_url . 'modules/everpsblog/views/img/';
        $this->context = Context::getContext();
        $this->identifier = 'id_ever_post';
        $this->_orderBy = $this->identifier;
        $this->_orderWay = 'DESC';
        $this->preview_token = Tools::encrypt('everpsblog/preview');
        $this->fields_list = [
            'id_ever_post' => [
                'title' => $this->l('ID'),
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
                'image' => 'post',
            ],
            'title' => [
                'title' => $this->l('Post title'),
                'align' => 'left',
                'havingFilter' => true,
                'filter_key' => 'l!title',
            ],
            'excerpt' => [
                'title' => $this->l('Post excerpt'),
                'align' => 'left',
            ],
            'cat_title' => [
                'title' => $this->l('Default category'),
                'align' => 'left',
                'havingFilter' => true,
                'filter_key' => 'acl!title',
            ],
            'nickhandle' => [
                'title' => $this->l('Author'),
                'align' => 'left',
                'width' => 25,
            ],
            'post_status' => [
                'title' => $this->l('Post status'),
                'align' => 'left',
                'width' => 25,
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
            'starred' => [
                'title' => $this->l('Starred'),
                'type' => 'bool',
                'active' => 'statusstarred',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
            'count' => [
                'title' => $this->l('Views count'),
                'align' => 'left',
            ],
        ];
        $this->unclassedCategory = (int) Configuration::get('EVERBLOG_UNCLASSED_ID');
        $this->colorOnBackground = true;
        $this->_select = 'l.title,
        l.excerpt,
        au.nickhandle,
        CONCAT("'.$this->img_url.'",ai.image_link) AS featured_img,
        acl.title AS cat_title';

        $this->_join =
            'LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post_lang` l
                ON (
                    l.`' . $this->identifier . '` = a.`' . $this->identifier . '`
                    AND l.`id_lang` = ' . (int) $this->context->language->id . '
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_author` au
                ON (
                    au.`id_ever_author` = a.`id_author`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_image` ai
                ON (
                    ai.`id_ever_image` = a.`' . $this->identifier . '`
                    AND ai.`image_type` = "post"
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_lang` acl
                ON (
                    acl.`id_ever_category` = a.`id_default_category`
                    AND acl.`id_lang` = ' . (int) $this->context->language->id . '
                )';
        $this->_where = 'AND a.id_shop = ' . (int) $this->context->shop->id;
        $this->_where = 'AND l.id_lang = ' . (int) $this->context->language->id;
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
        $blogUrl = $this->context->link->getModuleLink(
            'everpsblog',
            'blog',
            [],
            true
        );
        $ever_blog_token = Tools::encrypt('everpsblog/cron');
        $emptytrash = $this->context->link->getModuleLink(
            'everpsblog',
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
            'everpsblog',
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
            'everpsblog',
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
            'image_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . '/modules/everpsblog/views/img/',
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
            'Modules.Everpsblog.Admineverpsblogpostcontroller'
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
        $this->addRowAction('duplicate');
        $this->addRowAction('deletePost');
        $this->addRowAction('ViewPost');
        $this->addRowAction('UnprotectPost');
        if (Module::isInstalled('prettyblocks')
            && Module::isEnabled('prettyblocks')
        ) {
            $this->addRowAction('ConvertToPrettyBlocks');
        }
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?'),
            ],
            'duplicateall' => [
                'text' => $this->l('Duplicate selected items'),
                'confirm' => $this->l('Duplicate selected items ?'),
            ],
            'publishall' => [
                'text' => $this->l('Publish selected items'),
                'confirm' => $this->l('Publish selected items ?'),
            ],
            'draftall' => [
                'text' => $this->l('Draft selected items'),
                'confirm' => $this->l('Draft selected items ?'),
            ],
            'trashall' => [
                'text' => $this->l('Trash selected items'),
                'confirm' => $this->l('Trash selected items ?'),
            ],
            'unprotectall' => [
                'text' => $this->l('Remove password on selected items'),
                'confirm' => $this->l('Remove password on selected items ?'),
            ],
        ];
        if (Tools::getIsset('deletePost'.$this->table)) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            if (Validate::isLoadedObject($everObj)) {
                $everObj->delete();
            }
        }
        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $this->processBulkDelete();
        }
        if (Tools::isSubmit('submitBulkduplicateall' . $this->table)) {
            $this->processBulkDuplicate();
        }
        if (Tools::isSubmit('submitBulkpublishall' . $this->table)) {
            $this->processBulkPublish();
        }
        if (Tools::isSubmit('submitBulkdraftall' . $this->table)) {
            $this->processBulkDraft();
        }
        if (Tools::isSubmit('submitBulktrashall' . $this->table)) {
            $this->processBulkTrash();
        }
        if (Tools::isSubmit('submitBulkunprotectall' . $this->table)) {
            $this->processBulkUnprotect();
        }
        $lists = parent::renderList();
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
            . '/' . $this->module->name . '/views/templates/admin/headerController.tpl'
        );
        $blog_instance = Module::getInstanceByName($this->module->name);
        if ($blog_instance->checkLatestEverModuleVersion()) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_
                . '/'
                . $this->module->name
                . '/views/templates/admin/upgrade.tpl'
            );
        }
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_
            .'/' . $this->module->name . '/views/templates/admin/footer.tpl'
        );
        return $this->html;
    }

    protected function getConfigFormValues($obj)
    {
        $formValues = [];
        if (Validate::isLoadedObject($obj)) {
            $cat_taxonomies = EverPsBlogTaxonomy::getPostCategoriesTaxonomies(
                (int) $obj->id
            );
            $tag_taxonomies = EverPsBlogTaxonomy::getPostTagsTaxonomies(
                (int) $obj->id
            );
            $product_taxonomies = EverPsBlogTaxonomy::getPostProductsTaxonomies(
                (int) $obj->id
            );
            $formValues[] = [
                $this->identifier => (!empty(Tools::getValue($this->identifier)))
                ? Tools::getValue($this->identifier)
                : $obj->id,
                'title' => (!empty(Tools::getValue('title')))
                ? Tools::getValue('title')
                : $obj->title,
                'id_author' => (!empty(Tools::getValue('id_author')))
                ? Tools::getValue('id_author')
                : $obj->id_author,
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
                'excerpt' => (!empty(Tools::getValue('excerpt')))
                ? Tools::getValue('excerpt')
                : $obj->excerpt,
                'date_add' => (!empty(Tools::getValue('date_add')))
                ? Tools::getValue('date_add')
                : $obj->date_add,
                'date_upd' => (!empty(Tools::getValue('date_upd')))
                ? Tools::getValue('date_upd')
                : $obj->date_upd,
                'post_categories[]' => (!empty(Tools::getValue('post_categories')))
                ? Tools::getValue('post_categories')
                : $cat_taxonomies,
                'allowed_groups[]' => (!empty(Tools::getValue('allowed_groups')))
                ? Tools::getValue('allowed_groups')
                : json_decode($obj->allowed_groups),
                'id_default_category' => (!empty(Tools::getValue('id_default_category')))
                ? Tools::getValue('id_default_category')
                : $obj->id_default_category,
                'post_tags[]' => (!empty(Tools::getValue('post_tags')))
                ? Tools::getValue('post_tags')
                : $tag_taxonomies,
                'post_products[]' => (!empty(Tools::getValue('post_products')))
                ? Tools::getValue('post_products')
                : $product_taxonomies,
                'indexable' => (!empty(Tools::getValue('indexable')))
                ? Tools::getValue('indexable')
                : $obj->indexable,
                'follow' => (!empty(Tools::getValue('follow')))
                ? Tools::getValue('follow')
                : $obj->follow,
                'sitemap' => (!empty(Tools::getValue('sitemap')))
                ? Tools::getValue('sitemap')
                : $obj->sitemap,
                'starred' => (!empty(Tools::getValue('starred')))
                ? Tools::getValue('starred')
                : $obj->starred,
                'post_status' => (!empty(Tools::getValue('post_status')))
                ? Tools::getValue('post_status')
                : $obj->post_status,
                'psswd' => (!empty(Tools::getValue('psswd')))
                ? Tools::getValue('psswd')
                : '',
            ];
        } else {
            $cat_taxonomies = [];
            $tag_taxonomies = [];
            $product_taxonomies = [];
            $titles = [];
            $metaTitles = [];
            $metaDescriptions = [];
            $linkrewrite = [];
            $content = [];
            $excerpt = [];
            foreach (Language::getLanguages(false) as $lang) {
                $titles[$lang['id_lang']] = '';
                $metaTitles[$lang['id_lang']] = '';
                $metaDescriptions[$lang['id_lang']] = '';
                $linkrewrite[$lang['id_lang']] = '';
                $content[$lang['id_lang']] = '';
                $excerpt[$lang['id_lang']] = '';
            }
            $formValues[] = [
                $this->identifier => (!empty(Tools::getValue($this->identifier)))
                ? Tools::getValue($this->identifier)
                : '',
                'title' => (!empty(Tools::getValue('title')))
                ? Tools::getValue('title')
                : $titles,
                'id_author' => (!empty(Tools::getValue('id_author')))
                ? Tools::getValue('id_author')
                : '',
                'meta_title' => (!empty(Tools::getValue('meta_title')))
                ? Tools::getValue('meta_title')
                : $metaTitles,
                'meta_description' => (!empty(Tools::getValue('meta_description')))
                ? Tools::getValue('meta_description')
                : $metaDescriptions,
                'link_rewrite' => (!empty(Tools::getValue('link_rewrite')))
                ? Tools::getValue('link_rewrite')
                : $linkrewrite,
                'content' => (!empty(Tools::getValue('content')))
                ? Tools::getValue('content')
                : $content,
                'excerpt' => (!empty(Tools::getValue('excerpt')))
                ? Tools::getValue('excerpt')
                : $excerpt,
                'date_add' => (!empty(Tools::getValue('date_add')))
                ? Tools::getValue('date_add')
                : '',
                'date_upd' => (!empty(Tools::getValue('date_upd')))
                ? Tools::getValue('date_upd')
                : '',
                'post_categories[]' => (!empty(Tools::getValue('post_categories')))
                ? Tools::getValue('post_categories')
                :'',
                'allowed_groups[]' => (!empty(Tools::getValue('allowed_groups')))
                ? Tools::getValue('allowed_groups')
                :'',
                'id_default_category' => (!empty(Tools::getValue('id_default_category')))
                ? Tools::getValue('id_default_category')
                : '',
                'post_tags[]' => (!empty(Tools::getValue('post_tags')))
                ? Tools::getValue('post_tags')
                : '',
                'post_products[]' => (!empty(Tools::getValue('post_products')))
                ? Tools::getValue('post_products')
                : '',
                'indexable' => (!empty(Tools::getValue('indexable')))
                ? Tools::getValue('indexable')
                : '',
                'follow' => (!empty(Tools::getValue('follow')))
                ? Tools::getValue('follow')
                : '',
                'sitemap' => (!empty(Tools::getValue('sitemap')))
                ? Tools::getValue('sitemap')
                : '',
                'starred' => (!empty(Tools::getValue('starred')))
                ? Tools::getValue('starred')
                : '',
                'post_status' => (!empty(Tools::getValue('post_status')))
                ? Tools::getValue('post_status')
                : '',
                'psswd' => (!empty(Tools::getValue('psswd')))
                ? Tools::getValue('psswd')
                : '',
            ];
        }
        $values = call_user_func_array('array_merge', $formValues);
        return $values;
    }

    public function renderForm()
    {
        if ($this->context->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new element.');
            return false;
        }
        $groups = Group::getGroups(
            (int) $this->context->language->id
        );
        $post_id = Tools::getValue($this->identifier);
        $obj = new $this->className(
            (int) Tools::getValue($this->identifier)
        );

        $file_url = EverPsBlogImage::getBlogImageUrl(
            (int) $post_id,
            (int) $this->context->shop->id,
            'post'
        );
        $post_img = '<image src="'.(string) $file_url.'" style="max-width:150px;"/>';

        $post_status = [
            [
                'id_status' => 'draft',
                'name' => $this->l('draft'),
            ],
            [
                'id_status' => 'pending',
                'name' => $this->l('pending'),
            ],
            [
                'id_status' => 'published',
                'name' => $this->l('published'),
            ],
            [
                'id_status' => 'trash',
                'name' => $this->l('trash'),
            ],
            [
                'id_status' => 'planned',
                'name' => $this->l('planned'),
            ],
            [
                'id_status' => 'protected',
                'name' => $this->l('password protected'),
            ],
        ];

        $fields_form = [];
        if (Validate::isLoadedObject($obj)) {
            $link = new Link();
            $id_lang = (int) $this->context->language->id;
            $objectUrl = $link->getModuleLink(
                'everpsblog',
                'post',
                [
                    $this->identifier => $obj->id_ever_post,
                    'link_rewrite' => $obj->link_rewrite[$id_lang],
                    'preview' => $this->preview_token,
                ]
            );
            if (!empty($obj->psswd)) {
                $viewPostTxt = $this->l('See post (password protected)');
            } else {
                $viewPostTxt = $this->l('See post');
            }
            $object_html = '<a href="'
            . $objectUrl
            . '" target="_blank" class="btn btn-lg btn-info">'
            . $viewPostTxt
            . '</a>';
            $fields_form[] = [
                'form' => [
                    'input' => [
                        [
                            'type' => 'html',
                            'name' => 'view_obj',
                            'html_content' => $object_html,
                        ],
                    ],
                ],
            ];
        }

        $fields_form[] = [
            'form' => [
                'tinymce' => true,
                'description' => $this->l('Please specify your post informations'),
                'submit' => [
                    'name' => 'save',
                    'title' => $this->l('Save'),
                    'class' => 'btn pull-right',
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminEverPsBlogPost', true),
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
                        'type' => 'select',
                        'label' => $this->l('Associated categories'),
                        'desc' => $this->l('Please choose at least one category'),
                        'hint' => $this->l('Choose one or more categories'),
                        'name' => 'post_categories[]',
                        'class' => 'chosen',
                        'identifier' => 'name',
                        'multiple' => true,
                        'options' => [
                            'query' => EverPsBlogCategory::getAllCategories(
                                (int) $this->context->language->id,
                                (int) $this->context->shop->id
                            ),
                            'id' => 'id_ever_category',
                            'name' => 'title',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Default categories'),
                        'desc' => $this->l('Please choose default category'),
                        'hint' => $this->l('Will be used on breadcrumb'),
                        'name' => 'id_default_category',
                        'options' => [
                            'query' => EverPsBlogCategory::getAllCategories(
                                (int) $this->context->language->id,
                                (int) $this->context->shop->id,
                                1,
                                0,
                                true
                            ),
                            'id' => 'id_ever_category',
                            'name' => 'title',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Associated tags'),
                        'desc' => $this->l('Please choose at least one tag'),
                        'hint' => $this->l('Choose one or more tags'),
                        'name' => 'post_tags[]',
                        'class' => 'chosen',
                        'multiple' => true,
                        'options' => [
                            'query' => EverPsBlogTag::getAllTags(
                                (int) $this->context->language->id,
                                (int) $this->context->shop->id
                            ),
                            'id' => 'id_ever_tag',
                            'name' => 'title',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Associated products'),
                        'desc' => $this->l('Please choose at least one product'),
                        'hint' => $this->l('Choose one or more product'),
                        'name' => 'post_products[]',
                        'class' => 'chosen',
                        'multiple' => true,
                        'options' => [
                            'query' => Product::getProducts(
                                (int) $this->context->language->id,
                                0,
                                0,
                                'name',
                                'ASC'
                            ),
                            'id' => 'id_product',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Author'),
                        'desc' => $this->l('Please choose post author'),
                        'hint' => $this->l('Else will be shop name'),
                        'name' => 'id_author',
                        'options' => [
                            'query' => EverPsBlogAuthor::getAllAuthors(
                                (int) $this->context->language->id,
                                (int) $this->context->shop->id
                            ),
                            'id' => 'id_ever_author',
                            'name' => 'nickhandle',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Post meta title'),
                        'desc' => $this->l('Most of search engines do not accept more that 65 characters'),
                        'hint' => $this->l('Important for your SEO !'),
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
                        'label' => $this->l('Post meta description'),
                        'desc' => $this->l('Most of search engines do not accept more that 165 characters'),
                        'hint' => $this->l('Important for your SEO !'),
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
                        'label' => $this->l('Post link rewrite'),
                        'desc' => $this->l('For rewrite rules, required for SEO'),
                        'hint' => $this->l('Will set post base URL'),
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
                        'label' => $this->l('Post title'),
                        'desc' => $this->l('Add here post title'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'title',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Post content'),
                        'desc' => $this->l('Add here post content'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Post excerpt'),
                        'desc' => $this->l('Add here post excerpt'),
                        'hint' => $this->l('Will be shown on listings'),
                        'required' => true,
                        'name' => 'excerpt',
                        'lang' => true,
                        'autoload_rte' => false,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Post image'),
                        'desc' => $this->l('Will be shown on post top'),
                        'hint' => $this->l('Useful for sharing on social medias'),
                        'name' => 'post_image',
                        'display_image' => true,
                        'required' => true,
                        'image' => $post_img,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('SEO index post ?'),
                        'desc' => $this->l('Set yes to index, no to noindex'),
                        'hint' => $this->l('Else post won\'t be available on Google'),
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
                        'label' => $this->l('SEO follow post ?'),
                        'desc' => $this->l('Set yes to follow, no to nofollow'),
                        'hint' => $this->l('Nofollow will block search engines from following links on this post'),
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
                        'label' => $this->l('SEO sitemap post ?'),
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
                        'label' => $this->l('Star post on homepage ?'),
                        'desc' => $this->l('Set yes to set post on homepage'),
                        'hint' => $this->l('If no articles are highlighted on the home page, the latest articles will be displayed'),
                        'name' => 'starred',
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
                        'type' => 'datetime',
                        'label' => $this->l('Date add'),
                        'desc' => $this->l('Add here post date'),
                        'hint' => $this->l('Default date add will date post has been created'),
                        'required' => true,
                        'name' => 'date_add',
                        'lang' => false,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Post status'),
                        'desc' => $this->l('Select if published, draft or more'),
                        'hint' => $this->l('Pending is "waiting for review"'),
                        'name' => 'post_status',
                        'options' => [
                            'query' => $post_status,
                            'id' => 'id_status',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'password',
                        'label' => $this->l('Protect this post with password'),
                        'desc' => $this->l('If you enter a password here, the article will be protected. Don\'t forget to write down the password, it won\'t be shown to you again for security reasons!'),
                        'hint' => $this->l('Leave empty for no use'),
                        'required' => false,
                        'name' => 'psswd',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
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
        $this->fields_form = [];
        $helper->identifier = $this->identifier;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->token = Tools::getValue('token');
        $helper->submit_action = 'save';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues($obj),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int) $this->context->language->id,
        ];
        $helper->currentIndex = AdminController::$currentIndex;
        return $helper->generateForm($fields_form);
    }

    public function postProcess()
    {
        if (Tools::getIsset('unprotect_post')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            $everObj->psswd = null;
            $everObj->save();
        }
        if (Module::isInstalled('prettyblocks')
            && Module::isEnabled('prettyblocks')
            && Tools::getIsset('convert_to_prettyblocks')
        ) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            $everObj->convertToPrettyBlock();
        }
        if (Tools::getIsset('duplicateever_blog_post')) {
            return $this->duplicatePost(
                (int) Tools::getValue($this->identifier)
            );
        }
        if (Tools::getIsset('deleteever_blog_post')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            $everObj->delete();
        }
        if (Tools::getIsset('statusindexever_blog_post')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->indexable = !(int) $everObj->indexable;
            $everObj->save();
        }
        if (Tools::getIsset('statusfollowever_blog_post')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->follow = !(int) $everObj->follow;
            $everObj->save();
        }
        if (Tools::getIsset('statusstarredever_blog_post')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->starred = !(int) $everObj->starred;
            $everObj->save();
        }
        if (Tools::getIsset('statussitemapever_blog_post')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->sitemap = !(int) $everObj->sitemap;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue($this->identifier)) {
                $post = new $this->className();
            } else {
                $post = new $this->className(
                    (int) Tools::getValue($this->identifier)
                );
            }
            // Validate functions
            $post->id_shop = (int) $this->context->shop->id;
            // SEO
            if (Tools::getValue('indexable')
                && !Validate::isBool(Tools::getValue('indexable'))
            ) {
                 $this->errors[] = $this->l('Index is not valid');
            } else {
                $post->indexable = Tools::getValue('indexable');
            }
            if (Tools::getValue('follow')
                && !Validate::isBool(Tools::getValue('follow'))
            ) {
                 $this->errors[] = $this->l('Follow is not valid');
            } else {
                $post->follow = Tools::getValue('follow');
            }
            if (Tools::getValue('sitemap')
                && !Validate::isBool(Tools::getValue('sitemap'))
            ) {
                 $this->errors[] = $this->l('Follow is not valid');
            } else {
                $post->sitemap = Tools::getValue('sitemap');
            }
            if (Tools::getValue('sitemap')
                && !Validate::isBool(Tools::getValue('sitemap'))
            ) {
                 $this->errors[] = $this->l('Sitemap is not valid');
            } else {
                $post->sitemap = Tools::getValue('sitemap');
            }
            if (Tools::getValue('starred')
                && !Validate::isBool(Tools::getValue('starred'))
            ) {
                 $this->errors[] = $this->l('Starred is not valid');
            } else {
                $post->starred = Tools::getValue('starred');
            }
            // Date add
            if (!Tools::getValue('date_add')) {
                $post->date_add = date('Y-m-d H:i:s');
            }
            if (Tools::getValue('date_add')
                && !Validate::isDate(Tools::getValue('date_add'))
            ) {
                 $this->errors[] = $this->l('Date add is not valid');
            } else {
                $post->date_add = Tools::getValue('date_add');
            }
            if ($post->date_add > date('Y-m-d H:i:s')) {
                $post->post_status = 'planned';
            } else {
                $post->post_status = Tools::getValue('post_status');
            }
            // Author
            if (Tools::getValue('id_author')
                && !Validate::isInt(Tools::getValue('id_author'))
            ) {
                 $this->errors[] = $this->l('Author is not valid');
            } else {
                $post->id_author = Tools::getValue('id_author');
            }
            // Categories, products and tags
            // Default category cannot be root and defaults to unclassed
            $rootCategory = EverPsBlogCategory::getRootCategory();
            $idDefaultCategory = (int) Tools::getValue('id_default_category');
            if (!$idDefaultCategory || !Validate::isUnsignedInt($idDefaultCategory)) {
                $post->id_default_category = (int) $this->unclassedCategory;
            } elseif ($idDefaultCategory == (int) $rootCategory->id) {
                $this->errors[] = $this->l('Default category cannot be the root category');
            } else {
                $post->id_default_category = $idDefaultCategory;
            }
            $post_categories = Tools::getValue('post_categories');
            if (!is_array($post_categories)) {
                $post_categories = [$post_categories];
            }
            if (!in_array($post->id_default_category, $post_categories)) {
                $post_categories[] = (int) $post->id_default_category;
            }
            $post->post_categories = json_encode($post_categories);
            $post->allowed_groups = json_encode(Tools::getValue('allowed_groups'));
            $post->post_tags = json_encode(Tools::getValue('post_tags'));
            $post->post_products = json_encode(Tools::getValue('post_products'));
            $post->date_upd = date('Y-m-d H:i:s');
            if (Tools::getValue('post_status') != 'protected') {
                $post->psswd = null;
            } else {
                $post->psswd = md5(_COOKIE_KEY_ . Tools::getValue('psswd'));
            }
            // Multilingual fields
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('title_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('title_' . $lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Title is not valid for lang ') . $lang['id_lang'];
                } else {
                    $post->title[$lang['id_lang']] = Tools::getValue('title_' . $lang['id_lang']);
                }
                if (Tools::getValue('content_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('content_' . $lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Content is not valid for lang ') . $lang['id_lang'];
                } else {
                    $post->content[$lang['id_lang']] = Tools::getValue('content_' . $lang['id_lang']);
                }
                if (Tools::getValue('excerpt_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('excerpt_' . $lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Excerpt is not valid for lang ') . $lang['id_lang'];
                } else {
                    $post->excerpt[$lang['id_lang']] = Tools::getValue('excerpt_' . $lang['id_lang']);
                }
                if (Tools::getValue('meta_title_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('meta_title_' . $lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta title is not valid for lang ') . $lang['id_lang'];
                } else {
                    $post->meta_title[$lang['id_lang']] = Tools::getValue('meta_title_' . $lang['id_lang']);
                }
                if (Tools::getValue('meta_description_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('meta_description_' . $lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta description is not valid for lang ') . $lang['id_lang'];
                } else {
                    $post->meta_description[$lang['id_lang']] = Tools::getValue('meta_description_' . $lang['id_lang']);
                }
                if (!Tools::getValue('link_rewrite_' . $lang['id_lang'])
                    || !Validate::isLinkRewrite(Tools::getValue('link_rewrite_' . $lang['id_lang']))
                ) {
                    $post->link_rewrite[$lang['id_lang']] = Tools::str2url(
                        Tools::getValue('title_' . $lang['id_lang'])
                    );
                } else {
                    $post->link_rewrite[$lang['id_lang']] = Tools::str2url(
                        Tools::getValue('link_rewrite_' . $lang['id_lang'])
                    );
                }
            }
            if (!count($this->errors)) {
                try {
                    $post->save();
                    $post_img_link = 'img/post/'
                    . (int) $post->id
                    . '.jpg';
                    $ps_posts_destination = _PS_IMG_DIR_
                    . 'post/'
                    . (int) $post->id
                    . '.jpg';
                    if (!file_exists(_PS_IMG_DIR_ . 'post')) {
                        mkdir(_PS_IMG_DIR_ . 'post', 0755, true);
                    }
                    /* upload the image */
                    if (isset($_FILES['post_image'])
                        && isset($_FILES['post_image']['tmp_name'])
                        && !empty($_FILES['post_image']['tmp_name'])
                    ) {
                        Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
                        if (file_exists($ps_posts_destination)) {
                            unlink($ps_posts_destination);
                        }
                        if ($error = ImageManager::validateUpload($_FILES['post_image'])) {
                            $this->errors .= $error;
                        } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                            || !move_uploaded_file($_FILES['post_image']['tmp_name'], $tmp_name)
                        ) {
                            return false;
                        } elseif (!ImageManager::resize($tmp_name, $ps_posts_destination)) {
                            $this->errors .= $this->l(
                                'An error occurred while attempting to upload the image.'
                            );
                        }
                        if (isset($tmp_name)) {
                            unlink($tmp_name);
                        }
                        $featured_image = EverPsBlogImage::getBlogImage(
                            (int) $post->id,
                            (int) $this->context->shop->id,
                            'post'
                        );
                        if (!$featured_image) {
                            $featured_image = new EverPsBlogImage();
                        }
                        $featured_image->id_element = (int) $post->id;
                        $featured_image->image_type = 'post';
                        $featured_image->image_link = $post_img_link;
                        $featured_image->id_shop = (int) $this->context->shop->id;
                        return $featured_image->save();
                    }
                } catch (Exception $e) {
                    PrestaShopLogger::addLog($e->getMessage());
                }
            } else {
                $this->display = 'edit';
            }
        }
        Tools::clearCache();
        parent::postProcess();
    }

    public function displayDeletePostLink($token, $id_ever_post)
    {
        if (!$token) {
            return;
        }
        $drop_url = 'index.php?controller=AdminEverPsBlogPost';
        $drop_url .= '&deletePost'.$this->table;
        $drop_url .= '&id_ever_post='.$id_ever_post;
        $drop_url .= '&token=';
        $drop_url .= Tools::getAdminTokenLite('AdminEverPsBlogPost');
        $this->context->smarty->assign([
            'href' => $drop_url,
            'confirm' => $this->l('Delete post ?'),
            'action' => $this->l('Delete post'),
        ]);
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/helpers/lists/list_action_delete_post.tpl'
        );
    }

    public function displayViewPostLink($token, $id_ever_post)
    {
        if (!$token) {
            return;
        }
        $post = new $this->className($id_ever_post);
        $link = new Link();
        $id_lang = (int) $this->context->language->id;
        $see_url = $link->getModuleLink(
            $this->module->name,
            'post',
            [
                $this->identifier => $post->id,
                'link_rewrite' => $post->link_rewrite[$id_lang],
            ]
        );

        $this->context->smarty->assign([
            'href' => $see_url,
            'confirm' => null,
            'action' => $this->l('View post'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/helpers/lists/list_action_view_obj.tpl'
        );
    }

    public function displayUnprotectPostLink($token, $id_ever_post)
    {
        if (!$token) {
            return;
        }
        $post = new $this->className($id_ever_post);
        if (empty($post->psswd)) {
            return;
        }
        $link = new Link();
        $id_lang = (int) $this->context->language->id;
        $unprotect_url  = 'index.php?controller=AdminEverPsBlogPost&token=';
        $unprotect_url .= Tools::getAdminTokenLite('AdminEverPsBlogPost');
        $unprotect_url .= '&id_ever_post='.(int)$id_ever_post;
        $unprotect_url .= '&unprotect_post';

        $this->context->smarty->assign([
            'href' => $unprotect_url,
            'confirm' => null,
            'action' => $this->l('Unprotect post'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/helpers/lists/list_action_unprotect_obj.tpl'
        );
    }

    public function displayConvertToPrettyBlocksLink($token, $postId)
    {
        if (!$token) {
            return;
        }
        $convertToPrettyBlocksUrl  = 'index.php?controller=AdminEverPsBlogPost&token=';
        $convertToPrettyBlocksUrl .= Tools::getAdminTokenLite('AdminEverPsBlogPost');
        $convertToPrettyBlocksUrl .= '&id_ever_post=' . (int) $postId;
        $convertToPrettyBlocksUrl .= '&convert_to_prettyblocks';

        $this->context->smarty->assign([
            'href' => $convertToPrettyBlocksUrl,
            'confirm' => null,
            'action' => $this->l('Convert to Prettyblocks'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/helpers/lists/convert_to_prettyblocks.tpl'
        );
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

    protected function processBulkDuplicate()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $duplicate = true;
            if (!$this->duplicatePost((int) $idEverObj)) {
                $duplicate = false;
            }
        }
        return $duplicate;
    }

    protected function processBulkPublish()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new $this->className((int) $idEverObj);
            $everObj->post_status = 'published';
            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t publish the current object');
            }
        }
    }

    protected function processBulkDraft()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new $this->className((int) $idEverObj);
            $everObj->post_status = 'draft';
            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t draft the current object');
            }
        }
    }

    protected function processBulkUnprotect()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new $this->className((int) $idEverObj);
            $everObj->psswd = null;
            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t unprotect the current object');
            }
        }
    }

    protected function processBulkTrash()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new $this->className((int) $idEverObj);
            $everObj->post_status = 'trash';
            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t trash the current object');
            }
        }
    }

    protected function duplicatePost($id_ever_post)
    {
        try {
            $everObj = new $this->className(
                (int) $id_ever_post
            );
            $new_everObj = new $this->className();
            $new_everObj->id_lang = $everObj->id_lang;
            $new_everObj->id_shop = $everObj->id_shop;
            $new_everObj->id_author = $everObj->id_author;
            $new_everObj->indexable = $everObj->indexable;
            $new_everObj->follow = $everObj->follow;
            $new_everObj->sitemap = $everObj->sitemap;
            $new_everObj->starred = $everObj->starred;            
            $new_everObj->post_status = 'draft';
            $new_everObj->post_categories = $everObj->post_categories;
            $new_everObj->allowed_groups = $everObj->allowed_groups;
            $new_everObj->post_tags = $everObj->post_tags;
            $new_everObj->post_products = $everObj->post_products;
            $new_everObj->psswd = $everObj->psswd;
            $new_everObj->date_add = $everObj->date_add;
            $new_everObj->date_upd = $everObj->date_upd;
            foreach (Language::getLanguages(false) as $language) {
                $new_everObj->title[$language['id_lang']] = $everObj->title[$language['id_lang']];
                $new_everObj->content[$language['id_lang']] = $everObj->content[$language['id_lang']];
                $new_everObj->excerpt[$language['id_lang']] = $everObj->excerpt[$language['id_lang']];
                $new_everObj->meta_title[$language['id_lang']] = $everObj->meta_title[$language['id_lang']];
                $new_everObj->meta_description[$language['id_lang']] = $everObj->meta_description[$language['id_lang']];
                $new_everObj->link_rewrite[$language['id_lang']] = $everObj->link_rewrite[$language['id_lang']];
            }
            if ($new_everObj->save()) {
                $everObj_featured_image = EverPsBlogImage::getBlogImage(
                    (int) $everObj->id,
                    (int) $this->context->shop->id,
                    'post'
                );
                if (Validate::isLoadedObject($everObj_featured_image)) {
                    $new_everObj_featured_image = new EverPsBlogImage();
                    $new_everObj_featured_image->id_element = (int) $new_everObj->id;
                    $new_everObj_featured_image->image_type = 'post';
                    $post_img_link = 'img/post/'
                    .(int) $new_everObj->id
                    .'.jpg';
                    $new_everObj_featured_image->image_link = $post_img_link;
                    $new_everObj_featured_image->id_shop = (int) $everObj_featured_image->id_shop;
                    $new_everObj_featured_image->save();
                    // Copy featured image file
                    $old_ps_img = _PS_IMG_DIR_
                    . 'post/'
                    . (int) $everObj->id
                    . '.jpg';
                    if (file_exists($old_ps_img)) {
                        $old_img = Tools::getHttpHost(true)
                        . __PS_BASE_URI__
                        . 'img/post/'
                        . (int) $id_ever_post
                        . '.jpg';
                    } else {
                        $old_img = Tools::getHttpHost(true) . __PS_BASE_URI__.'/img/'.Configuration::get(
                            'PS_LOGO'
                        );
                    }
                    $new_ps_img = _PS_IMG_DIR_
                    . 'post/'
                    . (int) $new_everObj->id
                    . '.jpg';
                    if (copy($old_img, $new_ps_img)) {
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage());
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
