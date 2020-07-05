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

include_once(dirname(__FILE__).'/../../classes/controller/FrontController.php');
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlogpostModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;

    public function init()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->errors = array();
        $this->post = new EverPsBlogPost(
            (int)Tools::getValue('id_ever_post'),
            (int)$this->context->shop->id,
            (int)$this->context->language->id
        );
        parent::init();
        // if inactive post or unexists, redirect
        if (!(int)Tools::getValue('id_ever_post')
            || $this->post->post_status != 'published'
        ) {
            Tools::redirect('index.php?controller=404');
        }
    }

    protected function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans($string);
        }

        return parent::l($string, $specific, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('id_ever_post')) {
            $errors = array();
            $success = array();
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->post->index) {
                $seo_index = 'index';
            } else {
                $seo_index = 'noindex';
            }
            if ($this->post->follow) {
                $seo_follow = 'follow';
            } else {
                $seo_follow = 'nofollow';
            }
            $page = $this->context->controller->getTemplateVarPage();
            $page['meta']['robots'] = $seo_index . ', ' . $seo_follow;
            $page['meta']['title'] = $this->post->meta_title;
            $page['meta']['description'] = $this->post->meta_description;
            $this->context->smarty->assign('page', $page);

            // So we have a post, then let's check comments submitted
            if (Tools::isSubmit('everpostcomment')) {
                $ip_banned = explode(',', Configuration::get('EVERBLOG_BANNED_IP'));
                // Mokay, let's see your IP first
                foreach ($ip_banned as $banned_ip) {
                    if ($banned_ip == $_SERVER['REMOTE_ADDR']) {
                        $errors[] = $this->l('Wow ! What have you done ? You\'re banned from this blog !');
                    }
                }
                // So now, u're unlogged ? Right, email is required, must be unbanned
                if (!(bool)$this->context->customer->isLogged()) {
                    if (!Tools::getValue('customerEmail')
                        || !Validate::isEmail(Tools::getValue('customerEmail'))
                    ) {
                        $errors[] = $this->l('Error : The field "Email" is not valid');
                    } else {
                        $emails_banned = explode(',', Configuration::get('EVERBLOG_BANNED_USERS'));
                        foreach ($emails_banned as $banned_user) {
                            if ($banned_user == Tools::getValue('customerEmail')) {
                                $errors[] = $this->l('Wow ! What have you done ? You\'re banned from this blog !');
                            }
                        }
                    }
                }
                if (!Tools::getValue('RgpdCompliance')
                    || !Validate::isBool(Tools::getValue('RgpdCompliance'))
                ) {
                    $errors[] = $this->l('Error : The field "RGPD" is not valid');
                }
                if (!Tools::getValue('evercomment')
                    || !Validate::isCleanHtml(Tools::getValue('evercomment'))
                ) {
                    $errors[] = $this->l('Error : The field "comments" is not valid');
                }
                $comment = new EverPsBlogComment();
                $latest = $comment->getLatestCommentByEmail(
                    Tools::getValue('customerEmail'),
                    (int)$this->context->language->id
                );
                // Safety before : don't allow comment before specific time
                if ($latest->date_add
                    && strtotime($latest->date_add) >= strtotime('-30 minutes')
                ) {
                    $errors[] = $this->l('You must wait before sending another comment');
                }

                if (count($errors)) {
                    $this->context->smarty->assign(
                        array(
                            'errors' => $errors,
                        )
                    );
                } else {
                    $comment->id_ever_post = $this->post->id;
                    $comment->id_lang = $this->context->language->id;
                    if (!(bool)$this->context->customer->isLogged()) {
                        $customer = new Customer();
                        if ($customer->getByEmail(
                            Tools::getValue('customerEmail')
                        )) {
                            // Customer exists on this email, so what to do ?
                        }
                        $comment->user_email = Tools::getValue('customerEmail');
                        $comment->name = Tools::getValue('name');
                    } else {
                        $customer = new Customer(
                            (int)Context::getContext()->customer->id
                        );
                        $comment->user_email = $customer->email;
                        $comment->name = $customer->firstname;
                    }
                    $comment->comment = Tools::getValue('evercomment');
                    $comment->active = 0;
                    $comment->save();
                    // alert admin ! comment saved ! whouhouhouhou !
                    if ($this->sendCommentAlert((int)$comment->id)) {
                        $success[] = $this->l('Your comment has been submitted');
                        $this->context->smarty->assign(
                            array(
                                'successes' => $success,
                            )
                        );
                    } else {
                        $errors[] = $this->l('Email has not been sent to admin');
                        $this->context->smarty->assign(
                            array(
                                'errors' => $errors,
                            )
                        );
                    }
                }
            }
            // Now prepare template and show it
            $post_products = EverPsBlogCleaner::convertToArray(
                json_decode($this->post->post_products)
            );
            $count_products = count($post_products);
            $products = array();
            if (isset($post_products) && !empty($post_products)) {
                foreach ($post_products as $post_product) {
                    $pproduct = new Product(
                        (int)$post_product,
                        (int)$this->context->shop->id,
                        (int)$this->context->language->id
                    );
                    if ((bool)$pproduct->active === true) {
                        $pproduct_cover = Product::getCover(
                            (int)$pproduct->id
                        );
                        $pproduct->cover = (int)$pproduct_cover['id_image'];
                        $products[] = $pproduct;
                    }
                }
            }
            $post_tags = EverPsBlogCleaner::convertToArray(
                json_decode($this->post->post_tags)
            );
            $tags = array();
            if (isset($post_tags) && !empty($post_tags)) {
                foreach ($post_tags as $post_tag) {
                    $tags[] = new EverPsBlogTag(
                        (int)$post_tag,
                        (int)$this->context->shop->id,
                        (int)$this->context->language->id
                    );
                }
            }
            $commentsCount = EverPsBlogComment::commentsCount(
                (int)$this->post->id,
                (int)$this->context->language->id
            );
            $comments = EverPsBlogComment::getCommentsByPost(
                (int)$this->post->id,
                (int)$this->context->language->id
            );
            // Prepare shortcodes
            $this->post->content = EverPsBlogPost::changeShortcodes(
                (string)$this->post->content,
                (int)Context::getContext()->customer->id
            );
            $this->post->title = EverPsBlogPost::changeShortcodes(
                (string)$this->post->title,
                (int)Context::getContext()->customer->id
            );

            $this->context->smarty->assign(
                array(
                    'count_products' => $count_products,
                    'post' => $this->post,
                    'tags' => $tags,
                    'products' => $products,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => (int)$this->context->language->id,
                    'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/everpsblog/views/img/',
                    'allow_comments' => (bool)Configuration::get('EVERBLOG_ALLOW_COMMENTS'),
                    'animated' => (bool)$animate,
                    'logged' => (bool)$this->context->customer->isLogged(),
                    'comments' => (array)$comments,
                    'commentsCount' => (int)$commentsCount,
                )
            );
            if ($this->isSeven) {
                $this->setTemplate('module:everpsblog/views/templates/front/post.tpl');
            } else {
                $this->setTemplate('post.tpl');
            }
        }
    }

    public function getBreadcrumbLinks()
    {
        $this->post = new EverPsBlogPost(
            (int)Tools::getValue('id_ever_post'),
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->l('Blog'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'blog'
            ),
        );
        $categories = json_decode($this->post->post_categories);
        if ($categories) {
            foreach ($categories as $cat) {
                $category = new EverPsBlogCategory(
                    (int)$cat,
                    (int)$this->context->shop->id,
                    (int)$this->context->language->id
                );
                if (!$category->is_root_category) {
                    $breadcrumb['links'][] = array(
                        'title' => $category->title,
                        'url' => $this->context->link->getModuleLink(
                            'everpsblog',
                            'category',
                            array(
                                'id_ever_category' => $category->id,
                                'link_rewrite' => $category->link_rewrite
                            )
                        ),
                    );
                }
            }
        }
        $breadcrumb['links'][] = array(
            'title' => EverPsBlogPost::changeShortcodes(
                $this->post->title,
                Context::getContext()->customer->id
            ),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'post',
                array(
                    'id_ever_post' => $this->post->id,
                    'link_rewrite' => $this->post->link_rewrite
                )
            ),
        );
        return $breadcrumb;
    }

    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink(
            'everpsblog',
            'post',
            array(
                'id_ever_post' => $this->post->id,
                'link_rewrite' => $this->post->link_rewrite
            )
        );
    }

    protected function sendCommentAlert($id_ever_comment)
    {
        $employee = new Employee((int)Configuration::get('EVERBLOG_ADMIN_EMAIL'));
        $comment = new EverPsBlogComment(
            (int)$id_ever_comment
        );

        $mailDir = _PS_MODULE_DIR_.'everpsblog/mails/';
        $everShopEmail = Configuration::get('PS_SHOP_EMAIL');
        $mail = Mail::send(
            (int)$this->context->language->id,
            'everpsblog',
            $this->l('A new comment is pending'),
            array(
                '{shop_name}'=>Configuration::get('PS_SHOP_NAME'),
                '{shop_logo}'=>_PS_IMG_DIR_.Configuration::get(
                    'PS_LOGO',
                    null,
                    null,
                    (int)$this->context->shop->id
                ),
                '{comment}' => (string)$comment->comment,
                '{email}' => (string)$comment->user_email,
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
        return $mail;
    }

    public function getTemplateVarPage() {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog-post'] = true;
        return $page;
    }
}
