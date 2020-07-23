<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogAuthor.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTaxonomy.php';

class AdminEverPsBlogPostController extends ModuleAdminController
{
    private $html;
    public $name;

    public function __construct()
    {
        $this->name = 'AdminEverPsBlogPostController';
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->bootstrap = true;
        $this->display = 'Ever Blog Posts';
        $this->meta_title = $this->l('Ever Blog Posts');
        $this->table = 'ever_blog_post';
        $this->className = 'EverPsBlogPost';
        $this->context = Context::getContext();
        $this->identifier = "id_ever_post";
        $this->_orderBy = 'id_ever_post';
        $this->_orderWay = 'ASC';
        $this->fields_list = array(
            'id_ever_post' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 25
            ),
            'title' => array(
                'title' => $this->l('Post title'),
                'align' => 'left',
                'width' => 25
            ),
            'nickhandle' => array(
                'title' => $this->l('Author'),
                'align' => 'left',
                'width' => 25
            ),
            'post_status' => array(
                'title' => $this->l('Post status'),
                'align' => 'left',
                'width' => 25
            ),
            'index' => array(
                'title' => $this->l('Index'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
            'follow' => array(
                'title' => $this->l('Follow'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
        );

        $this->colorOnBackground = true;
        $this->_select = 'l.title, au.nickhandle';

        $this->_join =
            'LEFT JOIN `'._DB_PREFIX_.'ever_blog_post_lang` l
                ON (
                    l.`id_ever_post` = a.`id_ever_post`
                )
            LEFT JOIN `'._DB_PREFIX_.'ever_blog_author` au
                ON (
                    au.`id_ever_author` = a.`id_author`
                )';
        $this->_where = 'AND a.id_shop = '.(int)$this->context->shop->id;
        $this->_where = 'AND l.id_lang = '.(int)$this->context->language->id;
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
        $this->context->smarty->assign(array(
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
            return Context::getContext()->getTranslator()->trans($string);
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
        $this->addRowAction('duplicate');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?')
            ),
            'duplicateall' => array(
                'text' => $this->l('Duplicate selected items'),
                'confirm' => $this->l('Duplicate selected items ?')
            ),
        );

        if (Tools::isSubmit('submitBulkdelete'.$this->table)) {
            $this->processBulkDelete();
        }

        if (Tools::isSubmit('submitBulkduplicateall'.$this->table)) {
            $this->processBulkDuplicate();
        }
        if ((bool)Tools::getValue('deleteLogoImage') && (int)Tools::getValue('ever_blog_obj')) {
            $this->processDeleteObjImage(
                (int)Tools::getValue('ever_blog_obj')
            );
        }

        $lists = parent::renderList();

        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everpsblog/views/templates/admin/headerController.tpl'
        );
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everpsblog/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    protected function getConfigFormValues($obj)
    {
        $cat_taxonomies = EverPsBlogTaxonomy::getPostCategoriesTaxonomies(
            (int)$obj->id
        );
        if (isset($cat_taxonomies) && count($cat_taxonomies) > 0) {
            $cat_taxonomies = array_values(array_map('array_values', $cat_taxonomies));
            $cat_taxonomies = call_user_func_array('array_merge', $cat_taxonomies);
        }
        $tag_taxonomies = EverPsBlogTaxonomy::getPostTagsTaxonomies(
            (int)$obj->id
        );
        if (isset($tag_taxonomies) && count($tag_taxonomies) > 0) {
            $tag_taxonomies = array_values(array_map('array_values', $tag_taxonomies));
            $tag_taxonomies = call_user_func_array('array_merge', $tag_taxonomies);
        }
        $product_taxonomies = EverPsBlogTaxonomy::getPostProductsTaxonomies(
            (int)$obj->id
        );
        if (isset($product_taxonomies) && count($product_taxonomies) > 0) {
            $product_taxonomies = array_values(array_map('array_values', $product_taxonomies));
            $product_taxonomies = call_user_func_array('array_merge', $product_taxonomies);
        }
        $formValues = array();
        $formValues[] = array(
            'id_ever_post' => $obj->id,
            'title' => $obj->title,
            'id_author' => $obj->id_author,
            'meta_title' => $obj->meta_title,
            'meta_description' => $obj->meta_description,
            'link_rewrite' => $obj->link_rewrite,
            'content' => $obj->content,
            'date_add' => $obj->date_add,
            'date_upd' => $obj->date_upd,
            'post_categories[]' => $cat_taxonomies,
            'post_tags[]' => $tag_taxonomies,
            'post_products[]' => $product_taxonomies,
            'index' => json_decode($obj->index),
            'follow' => json_decode($obj->follow),
            'post_status' => $obj->post_status,
        );
        $values = call_user_func_array('array_merge', $formValues);
        return $values;
    }

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new backlink.');
        }

        if (count($this->errors)) {
            return false;
        }
        $post_id = Tools::getValue('id_ever_post');
        $obj = new EverPsBlogPost(
            (int)Tools::getValue('id_ever_post')
        );
        $fields_form = array();

        if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/posts/post_image_'.$post_id.'.jpg')) {
            $post_img = Tools::getHttpHost(true)
            .__PS_BASE_URI__
            .'modules/everpsblog/views/img/posts/post_image_'
            .$post_id
            .'.jpg';
        } else {
            $post_img = Tools::getHttpHost(true)
            .__PS_BASE_URI__
            .'/img/'
            .Configuration::get(
                'PS_LOGO'
            );
        }
        $post_img = '<image src="'.(string)$post_img.'" style="max-width:150px;"/>';

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

        if ($obj) {
            $link = new Link();
            $id_lang = (int)Context::getContext()->language->id;
            $objectUrl = $link->getModuleLink(
                'everpsblog',
                'post',
                array(
                    'id_ever_post' => $obj->id_ever_post ,'link_rewrite' => $obj->link_rewrite[$id_lang]
                )
            );
            $object_html = '<a href="'
            .$objectUrl
            .'" target="_blank" class="btn btn-default">'
            .$this->l('See post')
            .'</a>';
            $fields_form[] = array(
                'form' => array(
                    'input' => array(
                        array(
                            'type' => 'html',
                            'name' => 'view_obj',
                            'html_content' => $object_html,
                        ),
                    ),
                )
            );
        }

        $fields_form[] = array(
            'form' => array(
                'tinymce' => true,
                'description' => $this->l('Please specify your post informations'),
                'submit' => array(
                    'name' => 'save',
                    'title' => $this->l('Save'),
                    'class' => 'button pull-right'
                ),
                'buttons' => array(
                    array(
                        'href' => Context::getContext()->link->getAdminLink('AdminEverPsBlogPost', true),
                        'title' => $this->l('Cancel'),
                        'icon' => 'process-icon-cancel'
                    )
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_ever_post'
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Associated categories'),
                        'desc' => $this->l('Please choose at least one category'),
                        'hint' => $this->l('Choose one or more categories'),
                        'name' => 'post_categories[]',
                        'class' => 'chosen',
                        'identifier' => 'name',
                        'multiple' => true,
                        'options' => array(
                            'query' => EverPsBlogCategory::getAllCategories(
                                (int)$this->context->language->id,
                                (int)$this->context->shop->id
                            ),
                            'id' => 'id_ever_category',
                            'name' => 'title',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Associated tags'),
                        'desc' => $this->l('Please choose at least one tag'),
                        'hint' => $this->l('Choose one or more tags'),
                        'name' => 'post_tags[]',
                        'class' => 'chosen',
                        'multiple' => true,
                        'options' => array(
                            'query' => EverPsBlogTag::getAllTags(
                                (int)$this->context->language->id,
                                (int)$this->context->shop->id
                            ),
                            'id' => 'id_ever_tag',
                            'name' => 'title',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Associated products'),
                        'desc' => $this->l('Please choose at least one product'),
                        'hint' => $this->l('Choose one or more product'),
                        'name' => 'post_products[]',
                        'class' => 'chosen',
                        'multiple' => true,
                        'options' => array(
                            'query' => Product::getProducts(
                                (int)$this->context->language->id,
                                0,
                                0,
                                'name',
                                'ASC'
                            ),
                            'id' => 'id_product',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Author'),
                        'desc' => $this->l('Please choose post author'),
                        'hint' => $this->l('Else will be shop name'),
                        'name' => 'id_author',
                        'options' => array(
                            'query' => EverPsBlogAuthor::getAllAuthors(
                                (int)$this->context->language->id,
                                (int)$this->context->shop->id
                            ),
                            'id' => 'id_ever_author',
                            'name' => 'nickhandle',
                        ),
                    ),
                    array(
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
                        'rows' => 30
                    ),
                    array(
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
                        'rows' => 30
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Post link rewrite'),
                        'desc' => $this->l('For rewrite rules, required for SEO'),
                        'hint' => $this->l('Will set post base URL'),
                        'required' => true,
                        'name' => 'link_rewrite',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Post title'),
                        'desc' => $this->l('Add here post title'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'title',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Post content'),
                        'desc' => $this->l('Add here post content'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'content',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Post image'),
                        'desc' => $this->l('Will be shown on post top'),
                        'hint' => $this->l('Useful for sharing on social medias'),
                        'name' => 'post_image',
                        'display_image' => true,
                        'required' => true,
                        'image' => $post_img
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('SEO index post ?'),
                        'desc' => $this->l('Set yes to index, no to noindex'),
                        'hint' => $this->l('Else post won\'t be available on Google'),
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
                        'label' => $this->l('SEO follow post ?'),
                        'desc' => $this->l('Set yes to follow, no to nofollow'),
                        'hint' => $this->l('Nofollow will block search engines from following links on this post'),
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
                        'type' => 'datetime',
                        'label' => $this->l('Date add'),
                        'desc' => $this->l('Add here post date'),
                        'hint' => $this->l('Default date add will date post has been created'),
                        'required' => true,
                        'name' => 'date_add',
                        'lang' => false,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Post status'),
                        'desc' => $this->l('Select if published, draft or more'),
                        'hint' => $this->l('Pending is "waiting for review"'),
                        'name' => 'post_status',
                        'options' => array(
                            'query' => $post_status,
                            'id' => 'id_status',
                            'name' => 'name',
                        ),
                    ),
                )
            )
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->toolbar_scroll = true;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->token = Tools::getValue('token');
        $helper->submit_action = 'save';
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues($obj), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int)$this->context->language->id,
        );
        $helper->currentIndex = AdminController::$currentIndex;
        return $helper->generateForm($fields_form);
    }

    public function postProcess()
    {
        if (Tools::getIsset('duplicateever_blog_post')) {
            return $this->duplicatePost(
                (int)Tools::getValue('id_ever_post')
            );
        }
        if (Tools::getIsset('deleteever_blog_post')) {
            $everObj = new EverPsBlogPost(
                (int)Tools::getValue('id_ever_post')
            );
            $everObj->delete();
        }
        if (Tools::getIsset('statusever_blog_post')) {
            $everObj = new EverPsBlogPost(
                (int)Tools::getValue('id_ever_post')
            );
            (int)$everObj->active = !(int)$everObj->active;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue('id_ever_post')) {
                $post = new EverPsBlogPost();
            } else {
                $post = new EverPsBlogPost(
                    (int)Tools::getValue('id_ever_post')
                );
            }
            // Validate functions
            $post->id_shop = (int)$this->context->shop->id;
            // SEO
            if (Tools::getValue('index')
                && !Validate::isBool(Tools::getValue('index'))
            ) {
                 $this->errors[] = $this->l('Index is not valid');
            } else {
                $post->index = Tools::getValue('index');
            }
            if (Tools::getValue('follow')
                && !Validate::isBool(Tools::getValue('follow'))
            ) {
                 $this->errors[] = $this->l('Follow is not valid');
            } else {
                $post->follow = Tools::getValue('follow');
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
            $post->post_categories = json_encode(Tools::getValue('post_categories'));
            $post->post_tags = json_encode(Tools::getValue('post_tags'));
            $post->post_products = json_encode(Tools::getValue('post_products'));
            $post->date_upd = date('Y-m-d H:i:s');
            // Multilingual fields
            foreach (Language::getLanguages(false) as $lang) {
                if (!Tools::getValue('title_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('title_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Title is not valid for lang ').$lang['id_lang'];
                } else {
                    $post->title[$lang['id_lang']] = Tools::getValue('title_'.$lang['id_lang']);
                }
                if (!Tools::getValue('content_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('content_'.$lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Content is not valid for lang ').$lang['id_lang'];
                } else {
                    $post->content[$lang['id_lang']] = Tools::getValue('content_'.$lang['id_lang']);
                }
                if (!Tools::getValue('meta_title_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('meta_title_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta title is not valid for lang ').$lang['id_lang'];
                } else {
                    $post->meta_title[$lang['id_lang']] = Tools::getValue('meta_title_'.$lang['id_lang']);
                }
                if (!Tools::getValue('meta_description_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('meta_description_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta description is not valid for lang ').$lang['id_lang'];
                } else {
                    $post->meta_description[$lang['id_lang']] = Tools::getValue('meta_description_'.$lang['id_lang']);
                }
                if (!Tools::getValue('link_rewrite_'.$lang['id_lang'])
                    || !Validate::isLinkRewrite(Tools::getValue('link_rewrite_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Link rewrite is not valid for lang ').$lang['id_lang'];
                } else {
                    $post->link_rewrite[$lang['id_lang']] = Tools::getValue('link_rewrite_'.$lang['id_lang']);
                }
            }
            if (!count($this->errors)) {
                $post->save();
                $post_img_destination = _PS_MODULE_DIR_
                .'everpsblog/views/img/posts/post_image_'
                .(int)$post->id
                .'.jpg';
                /* upload the image */
                if (isset($_FILES['post_image']) && isset($_FILES['post_image']['tmp_name']) && !empty($_FILES['post_image']['tmp_name'])) {
                    Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
                    if (file_exists($post_img_destination)) {
                        unlink($post_img_destination);
                    }
                    if ($error = ImageManager::validateUpload($_FILES['post_image'])) {
                        $this->errors .= $error;
                    } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($_FILES['post_image']['tmp_name'], $tmp_name)) {
                        return false;
                    } elseif (!ImageManager::resize($tmp_name, $post_img_destination)) {
                        $this->errors .= $this->l(
                            'An error occurred while attempting to upload the image.'
                        );
                    }
                    if (isset($tmp_name)) {
                        unlink($tmp_name);
                        return true;
                    }
                } else {
                    if (file_exists($post_img_destination)) {
                        unlink($post_img_destination);
                    }
                    $logo = _PS_ROOT_DIR_.'/img/'.Configuration::get(
                        'PS_LOGO'
                    );
                    if (copy($logo, $post_img_destination)) {
                        return true;
                    }
                }
            }
        }
        parent::postProcess();
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everObj = new EverPsBlogPost((int)$idEverObj);

            if (!$everObj->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkDuplicate()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $duplicate = true;
            if (!$this->duplicatePost((int)$idEverObj)) {
                $duplicate = false;
            }
        }
        return $duplicate;
    }

    public function processDeleteObjImage($id_obj)
    {
        if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/posts/post_image_'.(int)$id_obj.'.jpg')) {
                $old_img = _PS_MODULE_DIR_.'everpsblog/views/img/posts/post_image_'.$id_obj.'.jpg';
                return unlink($old_img);
        }
    }

    protected function duplicatePost($id_ever_post)
    {
        $everObj = new EverPsBlogPost(
            (int)$id_ever_post
        );
        $new_everObj = new EverPsBlogPost();
        $new_everObj->id_lang = $everObj->id_lang;
        $new_everObj->id_shop = $everObj->id_shop;
        $new_everObj->id_author = $everObj->id_author;
        $new_everObj->index = $everObj->index;
        $new_everObj->follow = $everObj->follow;
        $new_everObj->post_status = $everObj->post_status;
        $new_everObj->post_categories = $everObj->post_categories;
        $new_everObj->post_tags = $everObj->post_tags;
        $new_everObj->post_products = $everObj->post_products;
        $new_everObj->date_add = $everObj->date_add;
        $new_everObj->date_upd = $everObj->date_upd;
        foreach (Language::getLanguages(false) as $language) {
            $new_everObj->title[$language['id_lang']] = $everObj->title[$language['id_lang']];
            $new_everObj->content[$language['id_lang']] = $everObj->content[$language['id_lang']];
            $new_everObj->meta_title[$language['id_lang']] = $everObj->meta_title[$language['id_lang']];
            $new_everObj->meta_description[$language['id_lang']] = $everObj->meta_description[$language['id_lang']];
            $new_everObj->link_rewrite[$language['id_lang']] = $everObj->link_rewrite[$language['id_lang']];
        }
        if ($new_everObj->save()) {
            $new_img = _PS_MODULE_DIR_.'everpsblog/views/img/posts/post_image_'.$new_everObj->id.'.jpg';
            if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/posts/post_image_'.$id_ever_post.'.jpg')) {
                $old_img = Tools::getHttpHost(true)
                .__PS_BASE_URI__
                .'modules/everpsblog/views/img/posts/post_image_'
                .$id_ever_post
                .'.jpg';
            } else {
                $old_img = Tools::getHttpHost(true).__PS_BASE_URI__.'/img/'.Configuration::get(
                    'PS_LOGO'
                );
            }
            if (copy($old_img, $new_img)) {
                return true;
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
