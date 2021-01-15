<?php
/**
 * 2019-2020 Team Ever
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

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogAuthor.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogImage.php';

class AdminEverPsBlogAuthorController extends ModuleAdminController
{
    private $html;
    public $name;

    public function __construct()
    {
        $this->name = 'AdminEverPsBlogAuthorController';
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->bootstrap = true;
        $this->display = 'Ever Blog Authors';
        $this->meta_title = $this->l('Ever Blog Authors');
        $this->table = 'ever_blog_author';
        $this->className = 'EverPsBlogAuthor';
        $this->module_name = 'everpsblog';
        $this->context = Context::getContext();
        $this->identifier = "id_ever_author";
        $this->_orderBy = 'id_ever_author';
        $this->_orderWay = 'DESC';
        $this->fields_list = array(
            'id_ever_author' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 25
            ),
            'nickhandle' => array(
                'title' => $this->l('Author nickhandle'),
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
            'active' => array(
                'title' => $this->l('Active'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
        );

        $this->colorOnBackground = true;
        // $this->_select = 'l.nickhandle';

        $this->_join =
            'LEFT JOIN `'._DB_PREFIX_.'ever_blog_author_lang` l
                ON (
                    l.`id_ever_author` = a.`id_ever_author`
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
        $ever_blog_token = Tools::encrypt('everpsblog/cron');
        $emptytrash = $this->context->link->getModuleLink(
            $this->module_name,
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
            $this->module_name,
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
            $this->module_name,
            'planned',
            array(
                'token' => $ever_blog_token,
                'id_shop' => (int)$this->context->shop->id
            ),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $this->context->smarty->assign(array(
            'image_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/everpsblog/views/img/',
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
                'Modules.Everpsblog.Admineverpsblogauthorcontroller'
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
        $this->addRowAction('ViewAuthor');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?')
            ),
        );

        if (Tools::isSubmit('submitBulkdelete'.$this->table)) {
            $this->processBulkDelete();
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
        $blog_instance = Module::getInstanceByName($this->module_name);
        if ($blog_instance->checkLatestEverModuleVersion($this->module_name, $blog_instance->version)) {
            $this->html .= $this->context->smarty->fetch(
                _PS_MODULE_DIR_ .'/everpsblog/views/templates/admin/upgrade.tpl');
        }
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everpsblog/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    protected function getConfigFormValues($obj)
    {
        $formValues = array();
        $formValues[] = array(
            'id_ever_author' => (!empty(Tools::getValue('id_ever_author'))) ? Tools::getValue('id_ever_author') : $obj->id,
            'nickhandle' => (!empty(Tools::getValue('nickhandle'))) ? Tools::getValue('nickhandle') : $obj->nickhandle,
            'meta_title' => (!empty(Tools::getValue('meta_title'))) ? Tools::getValue('meta_title') : $obj->meta_title,
            'meta_description' => (!empty(Tools::getValue('meta_description'))) ? Tools::getValue('meta_description') : $obj->meta_description,
            'link_rewrite' => (!empty(Tools::getValue('link_rewrite'))) ? Tools::getValue('link_rewrite') : $obj->link_rewrite,
            'twitter' => (!empty(Tools::getValue('twitter'))) ? Tools::getValue('twitter') : $obj->twitter,
            'facebook' => (!empty(Tools::getValue('facebook'))) ? Tools::getValue('facebook') : $obj->facebook,
            'linkedin' => (!empty(Tools::getValue('linkedin'))) ? Tools::getValue('linkedin') : $obj->linkedin,
            'content' => (!empty(Tools::getValue('content'))) ? Tools::getValue('content') : $obj->content,
            'date_add' => (!empty(Tools::getValue('date_add'))) ? Tools::getValue('date_add') : $obj->date_add,
            'date_upd' => (!empty(Tools::getValue('date_upd'))) ? Tools::getValue('date_upd') : $obj->date_upd,
            'index' => (!empty(Tools::getValue('index'))) ? Tools::getValue('index') : $obj->index,
            'follow' => (!empty(Tools::getValue('follow'))) ? Tools::getValue('follow') : $obj->follow,
            'active' => (!empty(Tools::getValue('active'))) ? Tools::getValue('active') : $obj->active,
        );
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
            (int)Tools::getValue('id_ever_author')
        );
        $fields_form = array();

        $file_url = EverPsBlogImage::getBlogImageUrl(
            (int)$author_id,
            (int)$this->context->shop->id,
            'author'
        );
        $author_img = '<image src="'.(string)$file_url.'" style="max-width:150px;"/>';

        if ($obj) {
            $link = new Link();
            $id_lang = (int)Context::getContext()->language->id;
            $objectUrl = $link->getModuleLink(
                'everpsblog',
                'author',
                array(
                    'id_ever_author' => $obj->id_ever_author ,'link_rewrite' => $obj->link_rewrite[$id_lang]
                )
            );
            $object_html = '<a href="'
            .$objectUrl
            .'" target="_blank" class="btn btn-default">'
            .$this->l('See author')
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
                'description' => $this->l('Please specify your author informations'),
                'submit' => array(
                    'name' => 'save',
                    'title' => $this->l('Save'),
                    'class' => 'button pull-right'
                ),
                'buttons' => array(
                    array(
                        'href' => Context::getContext()->link->getAdminLink('AdminEverPsBlogAuthor', true),
                        'title' => $this->l('Cancel'),
                        'icon' => 'process-icon-cancel'
                    )
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_ever_author'
                    ),
                    array(
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
                        'rows' => 30
                    ),
                    array(
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
                        'rows' => 30
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Author link rewrite'),
                        'desc' => $this->l('For rewrite rules, required for SEO'),
                        'hint' => $this->l('Will set author base URL'),
                        'required' => true,
                        'name' => 'link_rewrite',
                        'lang' => true,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Author nickhandle'),
                        'desc' => $this->l('Add here author nickhandle'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'nickhandle',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Author twitter'),
                        'desc' => $this->l('Add here author twitter'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'twitter',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Author facebook'),
                        'desc' => $this->l('Add here author facebook'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'facebook',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Author linkedin'),
                        'desc' => $this->l('Add here author linkedin'),
                        'hint' => $this->l('Will be shown on each pages'),
                        'required' => true,
                        'name' => 'linkedin',
                        'lang' => false,
                        'autoload_rte' => true,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Author content'),
                        'desc' => $this->l('Add here author content'),
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
                        'label' => $this->l('Author image'),
                        'desc' => $this->l('Will be shown on author top'),
                        'hint' => $this->l('Useful for sharing on social medias'),
                        'name' => 'author_image',
                        'display_image' => true,
                        'required' => true,
                        'image' => $author_img
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('SEO index author ?'),
                        'desc' => $this->l('Set yes to index, no to noindex'),
                        'hint' => $this->l('Else author won\'t be available on Google'),
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
                        'label' => $this->l('SEO follow author ?'),
                        'desc' => $this->l('Set yes to follow, no to nofollow'),
                        'hint' => $this->l('Nofollow will block search engines from following links on this author'),
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
                        'desc' => $this->l('Add here author date'),
                        'hint' => $this->l('Default date add will date author has been created'),
                        'required' => true,
                        'name' => 'date_add',
                        'lang' => false,
                        'cols' => 60,
                        'rows' => 30
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable author ?'),
                        'desc' => $this->l('Set yes to enable author, no to disable'),
                        'hint' => $this->l('Posts without authors will show shop name as author'),
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
            )
        );
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
        if (Tools::getValue('deleteever_blog_author')) {
            $everObj = new EverPsBlogAuthor(
                (int)Tools::getValue('id_ever_author')
            );
            $everObj->delete();
        }
        if (Tools::getValue('statusever_blog_author')) {
            $everObj = new EverPsBlogAuthor(
                (int)Tools::getValue('id_ever_author')
            );
            (int)$everObj->active = !(int)$everObj->active;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue('id_ever_author')) {
                $author = new EverPsBlogAuthor();
            } else {
                $author = new EverPsBlogAuthor(
                    (int)Tools::getValue('id_ever_author')
                );
            }
            // Validate functions
            $author->id_shop = (int)$this->context->shop->id;
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
            if (Tools::getValue('index')
                && !Validate::isBool(Tools::getValue('index'))
            ) {
                 $this->errors[] = $this->l('Index is not valid');
            } else {
                $author->index = Tools::getValue('index');
            }
            if (Tools::getValue('follow')
                && !Validate::isBool(Tools::getValue('follow'))
            ) {
                 $this->errors[] = $this->l('Follow is not valid');
            } else {
                $author->follow = Tools::getValue('follow');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                 $this->errors[] = $this->l('Follow is not valid');
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
                if (!Tools::getValue('content_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('content_'.$lang['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Content is not valid for lang ').$lang['id_lang'];
                } else {
                    $author->content[$lang['id_lang']] = Tools::getValue('content_'.$lang['id_lang']);
                }
                if (!Tools::getValue('meta_title_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('meta_title_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta title is not valid for lang ').$lang['id_lang'];
                } else {
                    $author->meta_title[$lang['id_lang']] = Tools::getValue('meta_title_'.$lang['id_lang']);
                }
                if (!Tools::getValue('meta_description_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('meta_description_'.$lang['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta description is not valid for lang ').$lang['id_lang'];
                } else {
                    $author->meta_description[$lang['id_lang']] = Tools::getValue('meta_description_'.$lang['id_lang']);
                }
                if (!Tools::getValue('link_rewrite_'.$lang['id_lang'])
                    || !Validate::isLinkRewrite(Tools::getValue('link_rewrite_'.$lang['id_lang']))
                ) {
                    $author->link_rewrite[$lang['id_lang']] = EverPsBlogCleaner::convertToUrlRewrite(
                        Tools::getValue('title_'.$lang['id_lang'])
                    );
                } else {
                    $author->link_rewrite[$lang['id_lang']] = Tools::getValue('link_rewrite_'.$lang['id_lang']);
                }
            }
            if (!count($this->errors)) {
                $author->save();
                $author_img_destination = _PS_MODULE_DIR_
                .'everpsblog/views/img/authors/author_image_'
                .(int)$author->id
                .'.jpg';
                $author_img_link = 'authors/author_image_'
                .(int)$author->id
                .'.jpg';
                /* upload the image */
                if (isset($_FILES['author_image'])
                    && isset($_FILES['author_image']['tmp_name'])
                    && !empty($_FILES['author_image']['tmp_name'])
                ) {
                    Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
                    if (file_exists($author_img_destination)) {
                        unlink($author_img_destination);
                    }
                    if ($error = ImageManager::validateUpload($_FILES['author_image'])) {
                        $this->errors .= $error;
                    } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                        || !move_uploaded_file($_FILES['author_image']['tmp_name'], $tmp_name)
                    ) {
                        return false;
                    } elseif (!ImageManager::resize($tmp_name, $author_img_destination)) {
                        $this->errors .= $this->l(
                            'An error occurred while attempting to upload the image.'
                        );
                    }
                    if (isset($tmp_name)) {
                        unlink($tmp_name);
                    }
                    $featured_image = EverPsBlogImage::getBlogImage(
                        (int)$author->id,
                        (int)Context::getContext()->shop->id,
                        'author'
                    );
                    if (!$featured_image) {
                        $featured_image = new EverPsBlogImage();
                    }
                    $featured_image->id_element = (int)$author->id;
                    $featured_image->image_type = 'author';
                    $featured_image->image_link = $author_img_link;
                    $featured_image->id_shop = (int)Context::getContext()->shop->id;
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
        $author = new EverPsBlogAuthor($id_ever_author);
        $link = new Link();
        $id_lang = (int)Context::getContext()->language->id;
        $see_url = $link->getModuleLink(
            'everpsblog',
            'author',
            array(
                'id_ever_author' => $author->id,
                'link_rewrite' => $author->link_rewrite[$id_lang]
            )
        );

        $this->context->smarty->assign(array(
            'href' => $see_url,
            'confirm' => null,
            'action' => $this->l('View author')
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'everpsblog/views/templates/admin/helpers/lists/list_action_view_order.tpl'
        );
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everObj = new EverPsBlogAuthor((int)$idEverObj);

            if (!$everObj->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    public function processDeleteObjImage($id_obj)
    {
        if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/authors/author_image_'.(int)$id_obj.'.jpg')) {
                $old_img = _PS_MODULE_DIR_.'everpsblog/views/img/authors/author_image_'.$id_obj.'.jpg';
                return unlink($old_img);
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
