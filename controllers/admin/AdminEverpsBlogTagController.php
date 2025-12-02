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
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogImage.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/controllers/admin/EverPsBlogAdminController.php';

class AdminEverPsBlogTagController extends EverPsBlogAdminController
{
    protected $html;

    public function __construct()
    {
        $this->name = 'AdminEverPsBlogTagController';
        $this->display = $this->l('Ever Blog Tags');
        $this->table = 'ever_blog_tag';
        $this->className = 'EverPsBlogTag';
        $this->identifier = 'id_ever_tag';
        $this->_orderBy = $this->identifier;
        $this->_orderWay = 'DESC';
        $this->fields_list = array(
            'id_ever_tag' => [
                'title' => $this->l('Tag ID'),
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
                'image' => 'tag',
            ],
            'title' => [
                'title' => $this->l('Tag title'),
                'align' => 'left',
            ],
            'posts_count' => [
                'title' => $this->l('Posts'),
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
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
            'active' => [
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'statusactive',
                'orderby' => false,
                'class' => 'fixed-width-sm',
            ],
            'count' => [
                'title' => $this->l('Views count'),
                'align' => 'left',
            ],
        );

        $this->colorOnBackground = true;
        $this->_select = 'l.title,
        (SELECT COUNT(*) FROM `'._DB_PREFIX_.'ever_blog_post_tag` pt WHERE pt.id_ever_post_tag = a.id_ever_tag) AS posts_count,
        CONCAT("' . $this->img_url . '",ai.image_link) AS featured_img';

        $this->_join =
            'LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_tag_lang` l
                ON (
                    l.`id_ever_tag` = a.`id_ever_tag`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_image` ai
                ON (
                    ai.`id_ever_image` = a.`id_ever_tag`
                    AND ai.`image_type` = "tag"
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
            'Modules.Everpsblog.Admineverpsblogtagcontroller'
        );
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
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('ViewTag');
        $this->toolbar_title = $this->l('Tags settings');
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
                'tag_products' => (!empty(Tools::getValue('tag_products')))
                ? Tools::getValue('tag_products')
                : json_decode($obj->tag_products),
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
                'tag_products' => (!empty(Tools::getValue('tag_products')))
                ? Tools::getValue('tag_products')
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

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new element.');
            return false;
        }
        
        $tag_id = Tools::getValue($this->identifier);
        $obj = new $this->className(
            (int) Tools::getValue($this->identifier)
        );

        $file_url = EverPsBlogImage::getBlogImageUrl(
            (int) $tag_id,
            (int) Context::getContext()->shop->id,
            'tag'
        );
        $groups = Group::getGroups(
            (int) Context::getContext()->language->id
        );
        $tagImg = '<image src="' . $file_url . '" style="max-width:150px;"/>';

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
                        'label' => $this->l('Tag meta description'),
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
                        'label' => $this->l('Tag link rewrite'),
                        'desc' => $this->l('For rewrite rules'),
                        'hint' => $this->l('Will set tag base URL'),
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
                        'label' => $this->l('Tag title'),
                        'desc' => $this->l('Please choose tag title'),
                        'hint' => $this->l('Will shown as title 1 on top of tag'),
                        'required' => true,
                        'name' => 'title',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Tag content'),
                        'desc' => $this->l('Please set tag content here'),
                        'hint' => $this->l('Tag content will be shown on top of page'),
                        'required' => true,
                        'name' => 'content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Tag bottom content'),
                        'desc' => $this->l('Please set tag bottom content here'),
                        'hint' => $this->l('Tag content will be shown on bottom of page'),
                        'required' => false,
                        'name' => 'bottom_content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l('Tag image'),
                        'desc' => $this->l('Featured tag image'),
                        'hint' => $this->l('If empty, your shop logo will be used'),
                        'name' => 'tag_image',
                        'display_image' => true,
                        'image' => $tagImg,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('SEO index tag ?'),
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
                        'label' => $this->l('SEO follow tag ?'),
                        'desc' => $this->l('Set yes to follow, no to nofollow'),
                        'hint' => $this->l('Do you want search engines to follow links on this tag ?'),
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
                        'label' => $this->l('SEO sitemap tag ?'),
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
                        'label' => $this->l('Activate tag ?'),
                        'desc' => $this->l('Set yes to activate'),
                        'hint' => $this->l('Else this tag won\'t be shown, but linked posts will'),
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

    public function postProcess()
    {
        if (Tools::getIsset('deleteever_blog_tag')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            $everObj->delete();
        }
        if (Tools::getIsset('statusactiveever_blog_tag')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->active = !(int) $everObj->active;
            $everObj->save();
        }
        if (Tools::getIsset('statusindexever_blog_tag')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->indexable = !(int) $everObj->indexable;
            $everObj->save();
        }
        if (Tools::getIsset('statusfollowever_blog_tag')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->follow = !(int) $everObj->follow;
            $everObj->save();
        }
        if (Tools::getIsset('statussitemapever_blog_tag')) {
            $everObj = new $this->className(
                (int) Tools::getValue($this->identifier)
            );
            (int) $everObj->sitemap = !(int) $everObj->sitemap;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue($this->identifier)) {
                $tag = new $this->className();
            } else {
                $tag = new $this->className(
                    (int) Tools::getValue($this->identifier)
                );
            }
            if (Tools::getValue('allowed_groups')
                && !Validate::isArrayWithIds(Tools::getValue('allowed_groups'))
            ) {
                $this->errors[] = $this->l('Allowed groups is not valid');
            } else {
                $tag->allowed_groups = json_encode(Tools::getValue('allowed_groups'));
            }
            if (Tools::getValue('indexable')
                && !Validate::isBool(Tools::getValue('indexable'))
            ) {
                $this->errors[] = $this->l('Index is not valid');
            } else {
                $tag->indexable = Tools::getValue('indexable');
            }
            if (Tools::getValue('follow')
                && !Validate::isBool(Tools::getValue('follow'))
            ) {
                $this->errors[] = $this->l('Follow is not valid');
            } else {
                $tag->follow = Tools::getValue('follow');
            }
            if (Tools::getValue('sitemap')
                && !Validate::isBool(Tools::getValue('sitemap'))
            ) {
                $this->errors[] = $this->l('Sitemap is not valid');
            } else {
                $tag->sitemap = Tools::getValue('sitemap');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                $this->errors[] = $this->l('Active is not valid');
            } else {
                $tag->active = Tools::getValue('active');
            }
            $tag->id_shop = (int) Context::getContext()->shop->id;
            if (!(int) Tools::getValue($this->identifier)) {
                $tag->date_add = date('Y-m-d H:i:s');
            }
            $tag->date_upd = date('Y-m-d H:i:s');
            // Multilingual fields
            foreach (Language::getLanguages(false) as $language) {
                if (Tools::getValue('title_'.$language['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('title_'.$language['id_lang']))
                ) {
                    $this->errors[] = $this->l('Title is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
                }
                if (Tools::getValue('content_'.$language['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('content_'.$language['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Content is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->content[$language['id_lang']] = Tools::getValue('content_'.$language['id_lang']);
                }
                if (Tools::getValue('bottom_content_'.$language['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('bottom_content_'.$language['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Content is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->bottom_content[$language['id_lang']] = Tools::getValue(
                        'bottom_content_'.$language['id_lang']
                    );
                }
                if (Tools::getValue('meta_title_'.$language['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('meta_title_'.$language['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta title is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->meta_title[$language['id_lang']] = Tools::getValue('meta_title_'.$language['id_lang']);
                }
                if (Tools::getValue('meta_description_'.$language['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('meta_description_'.$language['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta description is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->meta_description[$language['id_lang']] = Tools::getValue(
                        'meta_description_'.$language['id_lang']
                    );
                }
                if (Tools::getValue('link_rewrite_'.$language['id_lang'])
                    && !Validate::isLinkRewrite(Tools::getValue('link_rewrite_'.$language['id_lang']))
                ) {
                    $tag->link_rewrite[$language['id_lang']] = Tools::str2url(
                        Tools::getValue('title_'.$language['id_lang'])
                    );
                } else {
                    $tag->link_rewrite[$language['id_lang']] = Tools::str2url(
                        Tools::getValue('link_rewrite_'.$language['id_lang'])
                    );
                }
            }
            if (!count($this->errors)) {
                try {
                    $tag->save();
                    /* upload the image */
                    $tag_img_link = 'img/tag/'
                    . (int) $tag->id
                    . '.jpg';
                    $ps_tags_destination = _PS_IMG_DIR_
                    . 'tag/'
                    . (int) $tag->id
                    . '.jpg';
                    if (!file_exists(_PS_IMG_DIR_ . 'tag')) {
                        mkdir(_PS_IMG_DIR_ . 'tag', 0755, true);
                    }
                    if (isset($_FILES['tag_image'])
                        && isset($_FILES['tag_image']['tmp_name'])
                        && !empty($_FILES['tag_image']['tmp_name'])
                    ) {
                        Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
                        if (file_exists($ps_tags_destination)) {
                            unlink($ps_tags_destination);
                        }
                        if ($error = ImageManager::validateUpload($_FILES['tag_image'])) {
                            $this->errors .= $error;
                        } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                            || !move_uploaded_file($_FILES['tag_image']['tmp_name'], $tmp_name)
                        ) {
                            return false;
                        } elseif (!ImageManager::resize($tmp_name, $ps_tags_destination)) {
                            $this->errors .= $this->l(
                                'An error occurred while attempting to upload the image.'
                            );
                        }
                        if (isset($tmp_name)) {
                            unlink($tmp_name);
                        }
                        $featured_image = EverPsBlogImage::getBlogImage(
                            (int) $tag->id,
                            (int) Context::getContext()->shop->id,
                            'tag'
                        );
                        if (!$featured_image) {
                            $featured_image = new EverPsBlogImage();
                        }
                        $featured_image->id_element = (int) $tag->id;
                        $featured_image->image_type = 'tag';
                        $featured_image->image_link = $tag_img_link;
                        $featured_image->id_shop = (int) Context::getContext()->shop->id;
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

    public function displayViewTagLink($token, $id_ever_tag)
    {
        if (!$token) {
            return;
        }
        $tag = new $this->className($id_ever_tag);
        $link = new Link();
        $id_lang = (int) Context::getContext()->language->id;
        $see_url = $link->getModuleLink(
            $this->module->name,
            'tag',
            [
                'id_ever_tag' => $tag->id,
                'link_rewrite' => $tag->link_rewrite[$id_lang],
            ]
        );

        $this->context->smarty->assign([
            'href' => $see_url,
            'confirm' => null,
            'action' => $this->l('View tag'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/helpers/lists/list_action_view_obj.tpl'
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

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new $this->className((int) $idEverObj);
            if ($everObj->active) {
                $everObj->active = false;
            }
            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t disable the current object');
            }
        }
    }

    protected function processBulkEnable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $idEverObj) {
            $everObj = new $this->className((int) $idEverObj);
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
