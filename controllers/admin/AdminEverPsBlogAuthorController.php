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
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogImage.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/controllers/admin/EverPsBlogAdminController.php';

class AdminEverPsBlogAuthorController extends EverPsBlogAdminController
{
    protected $html;
    public $name;

    public function __construct()
    {
        $this->name = 'AdminEverPsBlogAuthorController';
        $this->display = $this->l('Ever Blog Authors');
        $this->table = 'ever_blog_author';
        $this->className = 'EverPsBlogAuthor';
        $this->identifier = 'id_ever_author';
        $this->_orderBy = $this->identifier;
        $this->_orderWay = 'DESC';
        $this->fields_list = [
            'id_ever_author' => [
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
                'image' => 'author',
            ],
            'posts_count' => [
                'title' => $this->l('Posts'),
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
            ],
            'nickhandle' => [
                'title' => $this->l('Author nickhandle'),
                'align' => 'left',
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
            'active' => [
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'statusactive',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
        ];

        $this->colorOnBackground = true;
        $this->_select = 'CONCAT("' . $this->img_url . '",ai.image_link) AS featured_img,
        (SELECT COUNT(*) FROM `'._DB_PREFIX_.'ever_blog_post` p WHERE p.id_author = a.id_ever_author) AS posts_count';

        $this->_join =
            'LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_author_lang` l
                ON (
                    l.`id_ever_author` = a.`id_ever_author`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_image` ai
                ON (
                    ai.`id_ever_image` = a.`id_ever_author`
                    AND ai.`image_type` = "author"
                )';
        $this->_where = 'AND a.id_shop = ' . (int) Context::getContext()->shop->id;
        $this->_where = 'AND l.id_lang = ' . (int) Context::getContext()->language->id;
        parent::__construct();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            'Modules.Everpsblog.Admineverpsblogauthorcontroller'
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
        $this->addRowAction('ViewAuthor');
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?'),
            ],
        ];
        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $this->processBulkDelete();
        }
        return parent::renderList();
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
                'id_ever_author' => (!empty(Tools::getValue('id_ever_author')))
                ? Tools::getValue('id_ever_author')
                : $obj->id,
                'allowed_groups[]' => (!empty(Tools::getValue('allowed_groups')))
                ? Tools::getValue('allowed_groups')
                : $allowedGroups,
                'nickhandle' => (!empty(Tools::getValue('nickhandle')))
                ? Tools::getValue('nickhandle')
                : $obj->nickhandle,
                'meta_title' => (!empty(Tools::getValue('meta_title')))
                ? Tools::getValue('meta_title')
                : $obj->meta_title,
                'meta_description' => (!empty(Tools::getValue('meta_description')))
                ? Tools::getValue('meta_description') : $obj->meta_description,
                'link_rewrite' => (!empty(Tools::getValue('link_rewrite')))
                ? Tools::getValue('link_rewrite')
                : $obj->link_rewrite,
                'twitter' => (!empty(Tools::getValue('twitter')))
                ? Tools::getValue('twitter')
                : $obj->twitter,
                'facebook' => (!empty(Tools::getValue('facebook')))
                ? Tools::getValue('facebook')
                : $obj->facebook,
                'linkedin' => (!empty(Tools::getValue('linkedin')))
                ? Tools::getValue('linkedin')
                : $obj->linkedin,
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
                'indexable' => (!empty(Tools::getValue('indexable')))
                ? Tools::getValue('indexable')
                : $obj->indexable,
                'follow' => (!empty(Tools::getValue('follow')))
                ? Tools::getValue('follow')
                : $obj->follow,
                'sitemap' => (!empty(Tools::getValue('sitemap')))
                ? Tools::getValue('sitemap')
                : $obj->sitemap,
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : $obj->active,
            ];
        } else {
            $metaTitles = [];
            $metaDescriptions = [];
            $linkrewrite = [];
            $content = [];
            $bottomContent = [];
            foreach (Language::getLanguages(false) as $lang) {
                $metaTitles[$lang['id_lang']] = '';
                $metaDescriptions[$lang['id_lang']] = '';
                $linkrewrite[$lang['id_lang']] = '';
                $content[$lang['id_lang']] = '';
                $bottomContent[$lang['id_lang']] = '';
            }
            $formValues[] = [
                'id_ever_author' => (!empty(Tools::getValue('id_ever_author')))
                ? Tools::getValue('id_ever_author')
                : '',
                'allowed_groups' => (!empty(Tools::getValue('allowed_groups')))
                ? Tools::getValue('allowed_groups')
                : '',
                'nickhandle' => (!empty(Tools::getValue('nickhandle')))
                ? Tools::getValue('nickhandle')
                : '',
                'meta_title' => (!empty(Tools::getValue('meta_title')))
                ? Tools::getValue('meta_title')
                : $metaTitles,
                'meta_description' => (!empty(Tools::getValue('meta_description')))
                ? Tools::getValue('meta_description') : $metaDescriptions,
                'link_rewrite' => (!empty(Tools::getValue('link_rewrite')))
                ? Tools::getValue('link_rewrite')
                : $linkrewrite,
                'twitter' => (!empty(Tools::getValue('twitter')))
                ? Tools::getValue('twitter')
                : '',
                'facebook' => (!empty(Tools::getValue('facebook')))
                ? Tools::getValue('facebook')
                : '',
                'linkedin' => (!empty(Tools::getValue('linkedin')))
                ? Tools::getValue('linkedin')
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
                'indexable' => (!empty(Tools::getValue('indexable')))
                ? Tools::getValue('indexable')
                : '',
                'follow' => (!empty(Tools::getValue('follow')))
                ? Tools::getValue('follow')
                : '',
                'sitemap' => (!empty(Tools::getValue('sitemap')))
                ? Tools::getValue('sitemap')
                : '',
                'active' => (!empty(Tools::getValue('active')))
                ? Tools::getValue('active')
                : '',
            ];
        }
        $values = call_user_func_array('array_merge', $formValues);
        return $values;
    }

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new element.');
            return false;
        }
        $author_id = Tools::getValue('id_ever_author');
        $obj = new EverPsBlogAuthor(
            (int) Tools::getValue('id_ever_author')
        );
        $fields_form = [];

        $file_url = EverPsBlogImage::getBlogImageUrl(
            (int) $author_id,
            (int) Context::getContext()->shop->id,
            'author'
        );
        $groups = Group::getGroups(
            (int) Context::getContext()->language->id
        );
        $author_img = '<image src="' . $file_url . '" style="max-width:150px;"/>';

        if (Validate::isLoadedObject($obj)) {
            $link = new Link();
            $id_lang = (int) Context::getContext()->language->id;
            $objectUrl = $link->getModuleLink(
                $this->module->name,
                'author',
                [
                    'id_ever_author' => $obj->id_ever_author ,
                    'link_rewrite' => $obj->link_rewrite[$id_lang],
                ],
            );
            $object_html = '<a href="'
            . $objectUrl
            . '" target="_blank" class="btn btn-default">'
            . $this->l('See author')
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
                'description' => $this->l('Please specify your author informations'),
                'submit' => [
                    'name' => 'save',
                    'title' => $this->l('Save'),
                    'class' => 'button pull-right',
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminEverPsBlogAuthor', true),
                        'title' => $this->l('Cancel'),
                        'icon' => 'process-icon-cancel',
                    ],
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_ever_author',
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
                        'label' => $this->l('Author meta title'),
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
                        'label' => $this->l('Author meta description'),
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
                        'label' => $this->l('Author link rewrite'),
                        'desc' => $this->l('For rewrite rules, required for SEO'),
                        'hint' => $this->l('Will set author base URL'),
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
                        'label' => $this->l('Author nickhandle'),
                        'desc' => $this->l('Add here author nickhandle'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'nickhandle',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Author twitter'),
                        'desc' => $this->l('Add here author twitter'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'twitter',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Author facebook'),
                        'desc' => $this->l('Add here author facebook'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'facebook',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Author linkedin'),
                        'desc' => $this->l('Add here author linkedin'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'linkedin',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Author content'),
                        'desc' => $this->l('Add here author content'),
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
                        'label' => $this->l('Author bottom content'),
                        'desc' => $this->l('Add here author bottom content'),
                        'hint' => $this->l('Will be shown on each bottom pages'),
                        'required' => false,
                        'name' => 'bottom_content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Author image'),
                        'desc' => $this->l('Will be shown on author top'),
                        'hint' => $this->l('Useful for sharing on social medias'),
                        'name' => 'author_image',
                        'display_image' => true,
                        'required' => true,
                        'image' => $author_img,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('SEO index author ?'),
                        'desc' => $this->l('Set yes to index, no to noindex'),
                        'hint' => $this->l('Else author won\'t be available on Google'),
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
                        'label' => $this->l('SEO follow author ?'),
                        'desc' => $this->l('Set yes to follow, no to nofollow'),
                        'hint' => $this->l('Nofollow will block search engines from following links on this author'),
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
                        'label' => $this->l('SEO sitemap author ?'),
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
                        'type' => 'datetime',
                        'label' => $this->l('Date add'),
                        'desc' => $this->l('Add here author date'),
                        'hint' => $this->l('Default date add will date author has been created'),
                        'required' => true,
                        'name' => 'date_add',
                        'lang' => false,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable author ?'),
                        'desc' => $this->l('Set yes to enable author, no to disable'),
                        'hint' => $this->l('Posts without authors will show shop name as author'),
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
        $this->fields_form = [];
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

    public function postProcess()
    {
        if (Tools::getValue('deleteever_blog_author')) {
            $everObj = new EverPsBlogAuthor(
                (int) Tools::getValue('id_ever_author')
            );
            $everObj->delete();
        }
        if (Tools::getIsset('statusactiveever_blog_author')) {
            $everObj = new EverPsBlogAuthor(
                (int) Tools::getValue('id_ever_author')
            );
            (int) $everObj->active = !(int) $everObj->active;
            $everObj->save();
        }
        if (Tools::getIsset('statusindexever_blog_author')) {
            $everObj = new EverPsBlogAuthor(
                (int) Tools::getValue('id_ever_author')
            );
            (int) $everObj->indexable = !(int) $everObj->indexable;
            $everObj->save();
        }
        if (Tools::getIsset('statusfollowever_blog_author')) {
            $everObj = new EverPsBlogAuthor(
                (int) Tools::getValue('id_ever_author')
            );
            (int) $everObj->follow = !(int) $everObj->follow;
            $everObj->save();
        }
        if (Tools::getIsset('statussitemapever_blog_author')) {
            $everObj = new EverPsBlogAuthor(
                (int) Tools::getValue('id_ever_author')
            );
            (int) $everObj->sitemap = !(int) $everObj->sitemap;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue('id_ever_author')) {
                $author = new EverPsBlogAuthor();
            } else {
                $author = new EverPsBlogAuthor(
                    (int) Tools::getValue('id_ever_author')
                );
            }
            // Validate functions
            $author->id_shop = (int) Context::getContext()->shop->id;
            if (Tools::getValue('allowed_groups')
                && !Validate::isArrayWithIds(Tools::getValue('allowed_groups'))
            ) {
                $this->errors[] = $this->l('Allowed groups is not valid');
            } else {
                $author->allowed_groups = json_encode(Tools::getValue('allowed_groups'));
            }
            if (!Tools::getValue('nickhandle')
                || !Validate::isCleanHtml(Tools::getValue('nickhandle'))
            ) {
                $this->errors[] = $this->l('Nickhandle is not valid');
            } else {
                $author->nickhandle = Tools::getValue('nickhandle');
            }
            if (Tools::getValue('linkedin')
                && !Validate::isCleanHtml(Tools::getValue('linkedin'))
            ) {
                $this->errors[] = $this->l('Nickhandle is not valid');
            } else {
                $author->linkedin = Tools::getValue('linkedin');
            }
            if (Tools::getValue('facebook')
                && !Validate::isCleanHtml(Tools::getValue('facebook'))
            ) {
                $this->errors[] = $this->l('Nickhandle is not valid');
            } else {
                $author->facebook = Tools::getValue('facebook');
            }
            if (Tools::getValue('twitter')
                && !Validate::isCleanHtml(Tools::getValue('twitter'))
            ) {
                $this->errors[] = $this->l('Nickhandle is not valid');
            } else {
                $author->twitter = Tools::getValue('twitter');
            }
            if (Tools::getValue('indexable')
                && !Validate::isBool(Tools::getValue('indexable'))
            ) {
                 $this->errors[] = $this->l('Index is not valid');
            } else {
                $author->indexable = Tools::getValue('indexable');
            }
            if (Tools::getValue('follow')
                && !Validate::isBool(Tools::getValue('follow'))
            ) {
                 $this->errors[] = $this->l('Follow is not valid');
            } else {
                $author->follow = Tools::getValue('follow');
            }
            if (Tools::getValue('sitemap')
                && !Validate::isBool(Tools::getValue('sitemap'))
            ) {
                 $this->errors[] = $this->l('Sitemap is not valid');
            } else {
                $author->sitemap = Tools::getValue('sitemap');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                 $this->errors[] = $this->l('Active is not valid');
            } else {
                $author->active = Tools::getValue('active');
            }
            // Date add
            if (!Tools::getValue('date_add')) {
                $author->date_add = date('Y-m-d H:i:s');
            }
            if (Tools::getValue('date_add')
                && !Validate::isDate(Tools::getValue('date_add'))
            ) {
                 $this->errors[] = $this->l('Date add is not valid');
            } else {
                $author->date_add = Tools::getValue('date_add');
            }

            $author->date_upd = date('Y-m-d H:i:s');
            // Multilingual fields
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('content_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('content_' . $lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Content is not valid for lang ') . $lang['id_lang'];
                } else {
                    $author->content[$lang['id_lang']] = Tools::getValue('content_' . $lang['id_lang']);
                }
                if (Tools::getValue('bottom_content_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('bottom_content_' . $lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Bottom content is not valid for lang ') . $lang['id_lang'];
                } else {
                    $author->bottom_content[$lang['id_lang']] = Tools::getValue('bottom_content_' . $lang['id_lang']);
                }
                if (Tools::getValue('meta_title_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('meta_title_' . $lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta title is not valid for lang ') . $lang['id_lang'];
                } else {
                    $author->meta_title[$lang['id_lang']] = Tools::getValue('meta_title_' . $lang['id_lang']);
                }
                if (Tools::getValue('meta_description_' . $lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('meta_description_' . $lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta description is not valid for lang ') . $lang['id_lang'];
                } else {
                    $author->meta_description[$lang['id_lang']] = Tools::getValue('meta_description_' . $lang['id_lang']);
                }
                if (Tools::getValue('link_rewrite_' . $lang['id_lang'])
                    && !Validate::isLinkRewrite(Tools::getValue('link_rewrite_' . $lang['id_lang']))
                ) {

                    $author->link_rewrite[$lang['id_lang']] = Tools::str2url(
                        Tools::getValue('title_' . $lang['id_lang'])
                    );
                } else {
                    $author->link_rewrite[$lang['id_lang']] = Tools::str2url(
                        Tools::getValue('link_rewrite_' . $lang['id_lang'])
                    );
                }
            }
            if (!count($this->errors)) {
                $author->save();
                $author_img_link = 'img/author/'
                . (int) $author->id
                . '.jpg';
                $ps_authors_destination = _PS_IMG_DIR_
                . 'author/'
                . (int) $author->id
                . '.jpg';
                if (!file_exists(_PS_IMG_DIR_ . 'author')) {
                    mkdir(_PS_IMG_DIR_ . 'author', 0755, true);
                }
                /* upload the image */
                if (isset($_FILES['author_image'])
                    && isset($_FILES['author_image']['tmp_name'])
                    && !empty($_FILES['author_image']['tmp_name'])
                ) {
                    Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
                    if (file_exists($ps_authors_destination)) {
                        unlink($ps_authors_destination);
                    }
                    if ($error = ImageManager::validateUpload($_FILES['author_image'])) {
                        $this->errors .= $error;
                    } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                        || !move_uploaded_file($_FILES['author_image']['tmp_name'], $tmp_name)
                    ) {
                        return false;
                    } elseif (!ImageManager::resize($tmp_name, $ps_authors_destination)) {
                        $this->errors .= $this->l(
                            'An error occurred while attempting to upload the image.'
                        );
                    }
                    if (isset($tmp_name)) {
                        unlink($tmp_name);
                    }
                    $featured_image = EverPsBlogImage::getBlogImage(
                        (int) $author->id,
                        (int) Context::getContext()->shop->id,
                        'author'
                    );
                    if (!$featured_image) {
                        $featured_image = new EverPsBlogImage();
                    }
                    $featured_image->id_element = (int) $author->id;
                    $featured_image->image_type = 'author';
                    $featured_image->image_link = $author_img_link;
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

    public function displayViewAuthorLink($token, $id_ever_author)
    {
        if (!$token) {
            return;
        }
        $author = new EverPsBlogAuthor($id_ever_author);
        $link = new Link();
        $id_lang = (int) Context::getContext()->language->id;
        $see_url = $link->getModuleLink(
            $this->module->name,
            'author',
            [
                'id_ever_author' => $author->id,
                'link_rewrite' => $author->link_rewrite[$id_lang],
            ]
        );

        $this->context->smarty->assign([
            'href' => $see_url,
            'confirm' => null,
            'action' => $this->l('View author'),
        ]);
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/helpers/lists/list_action_view_obj.tpl'
        );
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new EverPsBlogAuthor((int) $idEverObj);
            if (!$everObj->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
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
