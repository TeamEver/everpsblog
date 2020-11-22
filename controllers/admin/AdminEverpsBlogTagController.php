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

class AdminEverPsBlogTagController extends ModuleAdminController
{
    private $html;

    public function __construct()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->bootstrap = true;
        $this->display = 'Ever Blog Tags';
        $this->meta_title = $this->l('Ever Blog Tags');
        $this->table = 'ever_blog_tag';
        $this->className = 'EverPsBlogTag';
        $this->context = Context::getContext();
        $this->identifier = "id_ever_tag";
        $this->_orderBy = 'id_ever_tag';
        $this->_orderWay = 'DESC';
        $this->fields_list = array(
            'id_ever_tag' => array(
                'title' => $this->l('Tag ID'),
                'align' => 'left',
                'width' => 25
            ),
            'title' => array(
                'title' => $this->l('Tag title'),
                'align' => 'left',
                'width' => 25
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
        $this->_select = 'l.title';

        $this->_join =
            'LEFT JOIN `'._DB_PREFIX_.'ever_blog_tag_lang` l
                ON (
                    l.`id_ever_tag` = a.`id_ever_tag`
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
            return Context::getContext()->getTranslator()->trans($string, [],'Modules.Everpsblog.Admineverpsblogtagcontroller');
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
        $this->addRowAction('ViewTag');
        $this->toolbar_title = $this->l('Tags settings');
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
        if ((bool)Tools::getValue('deleteLogoImage') && (int)Tools::getValue('ever_blog_obj')) {
            $this->processDeleteObjImage(
                (int)Tools::getValue('ever_blog_obj')
            );
        }

        $lists = parent::renderList();

        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'/everpsblog/views/templates/admin/headerController.tpl'
        );
        $this->html .= $lists;
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . '/everpsblog/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    public function renderForm()
    {
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->errors[] = $this->l('You have to select a shop before creating or editing new element.');
            return false;
        }
        
        $tag_id = Tools::getValue('id_ever_tag');

        if (file_exists(
            _PS_MODULE_DIR_.'everpsblog/views/img/tags/tag_image_'.$tag_id.'.jpg'
        )) {
            $tag_img = Tools::getHttpHost(true)
            .__PS_BASE_URI__
            .'modules/everpsblog/views/img/tags/tag_image_'
            .(int)$tag_id
            .'.jpg';
        } else {
            $tag_img = Tools::getHttpHost(true)
            .__PS_BASE_URI__
            .'/img/'
            .Configuration::get(
                'PS_LOGO'
            );
        }
        // die(var_dump($tag_img));
        $tagImg = '<image src="'.(string)$tag_img.'" style="max-width:150px;"/>';

        // Building the Add/Edit form
        $this->fields_form = array(
            'tinymce' => true,
            'description' => $this->l('Please fill this form to set tag.'),
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Save'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Tag meta title'),
                    'desc'      => $this->l('More than 45 characters, less than 65'),
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
                    'label' => $this->l('Tag meta description'),
                    'desc' => $this->l('More than 90 characters, less than 165'),
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
                    'label' => $this->l('Tag link rewrite'),
                    'desc' => $this->l('For rewrite rules'),
                    'hint' => 'Will set tag base URL',
                    'required' => true,
                    'name' => 'link_rewrite',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Tag title'),
                    'desc'      => $this->l('Please choose tag title'),
                    'required' => true,
                    'name' => 'title',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Tag content'),
                    'desc'      => $this->l('Please set tag content'),
                    'required' => true,
                    'name' => 'content',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Tag image'),
                    'name' => 'tag_image',
                    'display_image' => true,
                    'image' => $tagImg
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('SEO index tag ?'),
                    'desc' => $this->l('Set yes to index, no to noindex'),
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
                    'label' => $this->l('SEO follow tag ?'),
                    'desc' => $this->l('Set yes to follow, no to nofollow'),
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
                    'label' => $this->l('Activate tag ?'),
                    'desc' => $this->l('Set yes to activate'),
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
        if (Tools::getIsset('deleteever_blog_category')) {
            $everObj = new EverPsBlogCategory(
                (int)Tools::getValue('id_ever_category')
            );
            (int)$everObj->active = !(int)$everObj->active;
            $everObj->delete();
        }
        if (Tools::getIsset('statusever_blog_category')) {
            $everObj = new EverPsBlogCategory(
                (int)Tools::getValue('id_ever_category')
            );
            (int)$everObj->active = !(int)$everObj->active;
            $everObj->save();
        }
        if (Tools::isSubmit('save')) {
            if (!Tools::getValue('id_ever_tag')) {
                $tag = new EverPsBlogTag();
            } else {
                $tag = new EverPsBlogTag(
                    (int)Tools::getValue('id_ever_tag')
                );
            }
            if (Tools::getValue('index')
                && !Validate::isBool(Tools::getValue('index'))
            ) {
                $this->errors[] = $this->l('Index is not valid');
            } else {
                $tag->index = Tools::getValue('index');
            }
            if (Tools::getValue('follow')
                && !Validate::isBool(Tools::getValue('follow'))
            ) {
                $this->errors[] = $this->l('Follow is not valid');
            } else {
                $tag->follow = Tools::getValue('follow');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                $this->errors[] = $this->l('Active is not valid');
            } else {
                $tag->active = Tools::getValue('active');
            }
            $tag->id_shop = (int)$this->context->shop->id;
            if (!(int)Tools::getValue('id_ever_tag')) {
                $tag->date_add = date('Y-m-d H:i:s');
            }
            $tag->date_upd = date('Y-m-d H:i:s');
            // Multilingual fields
            foreach (Language::getLanguages(false) as $language) {
                if (!Tools::getValue('title_'.$language['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('title_'.$language['id_lang']))
                ) {
                    $this->errors[] = $this->l('Title is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
                }
                if (!Tools::getValue('content_'.$language['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('content_'.$language['id_lang']), true)
                ) {
                    $this->errors[] = $this->l('Content is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->content[$language['id_lang']] = Tools::getValue('content_'.$language['id_lang']);
                }
                if (!Tools::getValue('meta_title_'.$language['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('meta_title_'.$language['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta title is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->meta_title[$language['id_lang']] = Tools::getValue('meta_title_'.$language['id_lang']);
                }
                if (!Tools::getValue('meta_description_'.$language['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('meta_description_'.$language['id_lang']))
                ) {
                    $this->errors[] = $this->l('Meta description is not valid for lang ').$language['id_lang'];
                } else {
                    $tag->meta_description[$language['id_lang']] = Tools::getValue(
                        'meta_description_'.$language['id_lang']
                    );
                }
                if (!Tools::getValue('link_rewrite_'.$lang['id_lang'])
                    || !Validate::isLinkRewrite(Tools::getValue('link_rewrite_'.$lang['id_lang']))
                ) {
                    $tag->link_rewrite[$lang['id_lang']] = EverPsBlogCleaner::convertToUrlRewrite(
                        Tools::getValue('title_'.$lang['id_lang'])
                    );
                } else {
                    $tag->link_rewrite[$lang['id_lang']] = Tools::getValue('link_rewrite_'.$lang['id_lang']);
                }
            }
            if (!count($this->errors)) {
                $tag->save();
                /* upload the image */
                $tag_img_destination = _PS_MODULE_DIR_
                .'everpsblog/views/img/tags/tag_image_'
                .(int)$tag->id
                .'.jpg';
                if (isset($_FILES['tag_image'])
                    && isset($_FILES['tag_image']['tmp_name'])
                    && !empty($_FILES['tag_image']['tmp_name'])
                ) {
                    Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
                    if (file_exists($tag_img_destination)) {
                        unlink($tag_img_destination);
                    }
                    if ($error = ImageManager::validateUpload($_FILES['tag_image'])) {
                        $this->errors .= $error;
                    } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                        || !move_uploaded_file($_FILES['tag_image']['tmp_name'], $tmp_name)
                    ) {
                        return false;
                    } elseif (!ImageManager::resize($tmp_name, $tag_img_destination)) {
                        $this->errors .= $this->l(
                            'An error occurred while attempting to upload the image.'
                        );
                    }
                    if (isset($tmp_name)) {
                        unlink($tmp_name);
                        return true;
                    }
                } else {
                    if (file_exists($tag_img_destination)) {
                        unlink($tag_img_destination);
                    }
                    $logo = _PS_ROOT_DIR_.'/img/'.Configuration::get(
                        'PS_LOGO'
                    );
                    if (copy($logo, $tag_img_destination)) {
                        return true;
                    }
                }
            } else {
                $this->display = 'edit';
            }
        }
        parent::postProcess();
    }

    public function displayViewTagLink($token, $id_ever_tag)
    {
        $tag = new EverPsBlogTag($id_ever_tag);
        $link = new Link();
        $id_lang = (int)Context::getContext()->language->id;
        $see_url = $link->getModuleLink(
            'everpsblog',
            'tag',
            array(
                'id_ever_tag' => $tag->id,
                'link_rewrite' => $tag->link_rewrite[$id_lang]
            )
        );

        $this->context->smarty->assign(array(
            'href' => $see_url,
            'confirm' => null,
            'action' => $this->l('View tag')
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'everpsblog/views/templates/admin/helpers/lists/list_action_view_order.tpl'
        );
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everObj = new EverPsBlogTag((int)$idEverObj);
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
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everObj = new EverPsBlogTag((int)$idEverObj);
            if (!$everObj->active) {
                $everObj->active = true;
            }

            if (!$everObj->save()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t enable the current object');
            }
        }
    }

    public function processDeleteObjImage($id_obj)
    {
        if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/tags/tag_image_'.(int)$id_obj.'.jpg')) {
                $old_img = _PS_MODULE_DIR_.'modules/everpsblog/views/img/tags/tag_image_'.$id_obj.'.jpg';
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
