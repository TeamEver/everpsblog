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

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogImage.php';

class AdminEverPsBlogCommentController extends ModuleAdminController
{
    private $html;

    public function __construct()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->bootstrap = true;
        $this->display = $this->l('Ever Blog Comments');
        $this->table = 'ever_blog_comments';
        $this->className = 'EverPsBlogComment';
        $this->module_name = 'everpsblog';
        $this->context = Context::getContext();
        $this->identifier = "id_ever_comment";
        $this->_orderBy = 'id_ever_comment';
        $this->_orderWay = 'DESC';

        $this->_select = 'pl.title AS pltitle';

        $this->_join =
            'LEFT JOIN `'._DB_PREFIX_.'ever_blog_post_lang` pl
                ON (
                    pl.`id_lang` = a.`id_lang`
                )';

        $this->_group = 'GROUP BY a.id_ever_comment';

        $this->fields_list = array(
            'id_ever_comment' => array(
                'title' => $this->l('Comment ID'),
                'align' => 'left',
                'width' => 25
            ),
            'pltitle' => array(
                'title' => $this->l('Post title'),
                'align' => 'left',
                'havingFilter' => true,
                'filter_key' => 'pl!title'
            ),
            'name' => array(
                'title' => $this->l('User name'),
                'align' => 'left'
            ),
            'user_email' => array(
                'title' => $this->l('User email'),
                'align' => 'left'
            ),
            'active' => array(
                'title' => $this->l('Comment status'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ),
        );

        $this->colorOnBackground = true;
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
                'Modules.Everpsblog.Admineverpsblogcommentcontroller'
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
        $this->addRowAction('deleteComment');
        $this->addRowAction('ViewPost');
        $this->toolbar_title = $this->l('Comment settings');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?')
            ),
        );

        if (Tools::getIsset('deleteComment'.$this->table)) {
            $everObj = new EverPsBlogComment(
                (int)Tools::getValue('id_ever_comment')
            );
            if (Validate::isLoadedObject($everObj)) {
                $everObj->delete();
            }
        }

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
        
        $posts_published = EverPsBlogPost::getPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        if (!$posts_published) {
            $this->errors[] = $this->l('There is no post, please create at least one');
            return;
        }
        // Building the Add/Edit form
        $this->fields_form = array(
            'tinymce' => true,
            'description' => $this->l('Please specify your comment informations'),
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Post'),
                    'desc' => $this->l('Comment is on this post'),
                    'hint' => $this->l('Select post\'s comment'),
                    'name' => 'id_ever_post',
                    'identifier' => 'title',
                    'options' => array(
                        'query' => $posts_published,
                        'id' => 'id_ever_post',
                        'name' => 'title',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Language'),
                    'desc' => $this->l('Comment is available only on this language'),
                    'hint' => $this->l('Select comment language'),
                    'name' => 'id_lang',
                    'identifier' => 'name',
                    'options' => array(
                        'query' => Language::getLanguages(true),
                        'id' => 'id_lang',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Comment'),
                    'desc' => $this->l('Please type or change post comment'),
                    'hint' => $this->l('As an administrator you can add HTML tags'),
                    'required' => true,
                    'name' => 'comment',
                    'lang' => false,
                    'autoload_rte' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('User name'),
                    'desc' => $this->l('Set or change user name'),
                    'hint' => $this->l('Required, please type a valid name'),
                    'required' => true,
                    'name' => 'name',
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('User email'),
                    'desc' => $this->l('Set or change user email'),
                    'hint' => $this->l('Required, please type a valid email'),
                    'required' => true,
                    'name' => 'user_email',
                    'lang' => false,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activate comment ?'),
                    'desc' => $this->l('Set yes to activate'),
                    'hint' => $this->l('Set "No" to disable this comment'),
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
        parent::postProcess();
        if (Tools::getValue('deleteever_blog_comments')) {
            $everObj = new EverPsBlogComment(
                (int)Tools::getValue('id_ever_comment')
            );
            (int)$everObj->active = !(int)$everObj->active;
            $everObj->delete();
        }
        if (Tools::getValue('statusever_blog_comments')) {
            $everObj = new EverPsBlogComment(
                (int)Tools::getValue('id_ever_comment')
            );
            (int)$everObj->active = !(int)$everObj->active;
            $everObj->save();
        }
        if ((bool)Tools::isSubmit('save')) {
            if (!Tools::getValue('id_ever_comment')) {
                $comment = new EverPsBlogComment();
            } else {
                $comment = new EverPsBlogComment(
                    (int)Tools::getValue('id_ever_comment')
                );
            }
            if (!Tools::getValue('id_lang')
                || !Validate::isUnsignedInt(Tools::getValue('id_lang'))
            ) {
                $this->errors[] = $this->l('Lang is not valid');
            } else {
                $comment->id_lang = Tools::getValue('id_lang');
            }
            if (!Tools::getValue('comment')
                || !Validate::isCleanHtml(Tools::getValue('comment'))
            ) {
                $this->errors[] = $this->l('Comment is not valid');
            } else {
                $comment->comment = Tools::getValue('comment');
            }
            if (!Tools::getValue('name')
                || !Validate::isGenericName(Tools::getValue('name'))
            ) {
                $this->errors[] = $this->l('User name is not valid');
            } else {
                $comment->name = Tools::getValue('name');
            }
            if (!Tools::getValue('user_email')
                || !Validate::isEmail(Tools::getValue('user_email'))
            ) {
                $this->errors[] = $this->l('User email is not valid');
            } else {
                $comment->user_email = Tools::getValue('user_email');
            }
            if (!Tools::getValue('id_ever_post')
                || !Validate::isUnsignedInt(Tools::getValue('id_ever_post'))
            ) {
                $this->errors[] = $this->l('Post is not valid');
            } else {
                $comment->id_ever_post = Tools::getValue('id_ever_post');
            }
            if (Tools::getValue('active')
                && !Validate::isBool(Tools::getValue('active'))
            ) {
                $this->errors[] = $this->l('Active is not valid');
            } else {
                $comment->active = Tools::getValue('active');
            }
            if (!count($this->errors)) {
                $comment->save();
            } else {
                $this->display = 'edit';
            }
        }
        Tools::clearCache();
    }

    public function displayDeleteCommentLink($token, $id_ever_comment)
    {
        if (!$token) {
            return;
        }
        $drop_url  = 'index.php?controller=AdminEverPsBlogComment';
        $drop_url  .= '&deleteComment'.$this->table;
        $drop_url  .= '&id_ever_comment='.$id_ever_comment;
        $drop_url  .= '&token=';
        $drop_url .= Tools::getAdminTokenLite('AdminEverPsBlogComment');

        $this->context->smarty->assign(array(
            'href' => $drop_url,
            'confirm' => $this->l('Delete comment ?'),
            'action' => $this->l('Delete comment')
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'everpsblog/views/templates/admin/helpers/lists/list_action_delete_post.tpl'
        );
    }

    public function displayViewPostLink($token, $id_ever_comment)
    {
        if (!$token) {
            return;
        }
        $comment = new EverPsBlogComment(
            (int)$id_ever_comment
        );
        $post = new EverPsBlogPost($comment->id_ever_post);
        $link = new Link();
        $id_lang = (int)Context::getContext()->language->id;
        $see_url = $link->getModuleLink(
            'everpsblog',
            'post',
            array(
                'id_ever_post' => $post->id,
                'link_rewrite' => $post->link_rewrite[$id_lang]
            )
        );

        $this->context->smarty->assign(array(
            'href' => $see_url,
            'confirm' => null,
            'action' => $this->l('View post')
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'everpsblog/views/templates/admin/helpers/lists/list_action_view_obj.tpl'
        );
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everObj = new EverPsBlogComment((int)$idEverObj);

            if (!$everObj->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }

    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everObj = new EverPsBlogComment((int)$idEverObj);
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
            $everObj = new EverPsBlogComment((int)$idEverObj);
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
