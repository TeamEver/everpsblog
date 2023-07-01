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

require_once(dirname(__FILE__).'/../../classes/controller/FrontController.php');

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
    protected $author;
    public $controller_name = 'post';

    public function init()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->module_name = 'everpsblog';
        $this->ip_banned = explode(',', Configuration::get('EVERBLOG_BANNED_IP'));
        $this->users_banned = explode(',', Configuration::get('EVERBLOG_BANNED_USERS'));
        if (in_array($_SERVER['REMOTE_ADDR'], $this->ip_banned)) {
            $this->allow_comments = false;
        } else {
            $this->allow_comments = (bool)Configuration::get('EVERBLOG_ALLOW_COMMENTS');
        }
        $this->errors = [];
        $this->post = new EverPsBlogPost(
            (int)Tools::getValue('id_ever_post'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        if (isset($this->post->id_author) && (int) $this->post->id_author > 0) {
            $this->author = new EverPsBlogAuthor(
                (int) $this->post->id_author,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            if ((bool) $this->author->active === true) {
                $this->author->url = $this->context->link->getModuleLink(
                    'everpsblog',
                    'author',
                    array(
                        'id_ever_author' => $this->author->id,
                        'link_rewrite' => $this->author->link_rewrite
                    )
                );
            } else {
                $this->author = new stdClass();
                $this->author->id_ever_author = 0;
                $this->author->id = 0;
                $this->author->nickhandle = Configuration::get('PS_SHOP_NAME');
                $this->author->url = Tools::getHttpHost(true) . __PS_BASE_URI__;
            }
        } else {
            $this->author = new stdClass();
            $this->author->id_ever_author = 0;
            $this->author->id = 0;
            $this->author->nickhandle = Configuration::get('PS_SHOP_NAME');
            $this->author->url = Tools::getHttpHost(true) . __PS_BASE_URI__;
        }
        if ($this->post->psswd && !empty($this->post->psswd)) {
            // code...
        }
        // Get author cover if exists, else get shop logo
        $this->author_cover = EverPsBlogImage::getBlogImageUrl(
            (int) $this->author->id,
            (int) Context::getContext()->shop->id,
            'author'
        );
        $this->post_tags = EverPsBlogTaxonomy::getPostTagsTaxonomies(
            (int) $this->post->id
        );
        $this->post_categories = EverPsBlogTaxonomy::getPostCategoriesTaxonomies(
            (int) $this->post->id
        );
        $this->post_products = EverPsBlogTaxonomy::getPostProductsTaxonomies(
            (int) $this->post->id
        );
        parent::init();
        // if inactive post or unexists, redirect
        if (!Tools::getValue('id_ever_post')) {
            Tools::redirect('index.php?controller=404');
        }
        if (Tools::getValue('preview') != Tools::encrypt('everpsblog/preview')
            && $this->post->post_status != 'published'
        ) {
            Tools::redirect('index.php?controller=404');
        }
        if (!Tools::getValue('preview')) {
            if ((bool) Tools::isSubmit('everpostcomment') === false) {
                EverPsBlogPost::updatePostViewCount(
                    (int) $this->post->id,
                    (int) $this->context->shop->id
                );
            }
        }
    }

    public function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsblog.post'
            );
        }

        return parent::l($string, $specific, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('id_ever_post')) {
            $errors = [];
            $success = [];
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
                // Mokay, let's see your IP first
                if (in_array($_SERVER['REMOTE_ADDR'], $this->ip_banned)) {
                    $errors[] = $this->l(
                        'Wow ! What have you done ? You\'re banned from this blog !'
                    );
                }
                // So now, u're unlogged ? Right, email is required, must be unbanned
                if (!(bool) $this->context->customer->isLogged()) {
                    if (!Tools::getValue('customerEmail')
                        || !Validate::isEmail(Tools::getValue('customerEmail'))
                    ) {
                        $errors[] = $this->l('Error : The field "Email" is not valid');
                    } else {
                        if (in_array(Tools::getValue('customerEmail'), $this->users_banned)) {
                            $errors[] = $this->l(
                                'Wow ! What have you done ? You\'re banned from this blog !'
                            );
                        }
                    }
                    if (!Tools::getValue('name')
                        || !Validate::isCleanHtml(Tools::getValue('name'))
                    ) {
                        $errors[] = $this->l('Error : The field "name" is not valid');
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
                    (int) $this->context->language->id
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
                    if (!(bool) $this->context->customer->isLogged()) {
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
                            (int) Context::getContext()->customer->id
                        );
                        $comment->user_email = $customer->email;
                        $comment->name = $customer->firstname;
                    }
                    $comment->comment = Tools::getValue('evercomment');
                    $comment->active = 0;
                    $comment->save();
                    // alert admin ! comment saved ! whouhouhouhou !
                    if ($this->sendCommentAlert((int) $comment->id)) {
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
            $ps_products = [];
            if (isset($this->post_products) && !empty($this->post_products)) {
                $showPrice = true;
                $assembler = new ProductAssembler(Context::getContext());
                $presenterFactory = new ProductPresenterFactory(Context::getContext());
                $presentationSettings = $presenterFactory->getPresentationSettings();
                $presenter = new ProductListingPresenter(
                    new ImageRetriever(
                        Context::getContext()->link
                    ),
                    Context::getContext()->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    Context::getContext()->getTranslator()
                );
                $presentationSettings->showPrices = $showPrice;
                foreach ($this->post_products as $post_product) {
                    $pproduct = new Product(
                        (int) $post_product['id_ever_post_product'],
                        true,
                        (int) Context::getContext()->language->id,
                        (int) Context::getContext()->shop->id
                    );
                    if (Product::checkAccessStatic((int) $pproduct->id, false)) {
                        $pproduct_cover = Product::getCover(
                            (int) $pproduct->id
                        );
                        $pproduct->cover = (int) $pproduct_cover['id_image'];
                        $ps_products[] = $presenter->present(
                            $presentationSettings,
                            $assembler->assembleProduct(array('id_product' => $pproduct->id)),
                            Context::getContext()->language
                        );
                    }
                }
            }
            $count_products = count($ps_products);
            $tags = [];
            if (isset($this->post_tags) && !empty($this->post_tags)) {
                foreach ($this->post_tags as $post_tag) {
                    $current_post_tag = new EverPsBlogTag(
                        (int) $post_tag['id_ever_post_tag'],
                        (int) $this->context->shop->id,
                        (int) $this->context->language->id
                    );
                    if ((bool) $current_post_tag->active === true) {
                        $tags[] = $current_post_tag;
                    }
                }
            }
            $commentsCount = EverPsBlogComment::commentsCount(
                (int) $this->post->id,
                (int) $this->context->language->id
            );
            $comments = EverPsBlogComment::getCommentsByPost(
                (int) $this->post->id,
                (int) $this->context->language->id
            );
            // die(var_dump($this->post->psswd));
            // Password protected
            $cookieName = $this->context->shop->id . $this->post->id . Tools::encrypt('everpsblog/post-' . $this->post->id);
            if ($this->post->psswd
                && !empty($this->post->psswd)
                && !$this->context->cookie->__isset($cookieName)
            ) {
                if (Tools::getValue('post_psswd')) {
                    if ($this->post->checkPassword($this->post->id, md5(_COOKIE_KEY_ . Tools::getValue('post_psswd'))) === false) {
                        $this->post->password_protected = true;
                        $this->post->content = $this->l('This post is password protected');
                    }
                    if ($this->post->checkPassword($this->post->id, md5(_COOKIE_KEY_ . Tools::getValue('post_psswd'))) === true) {
                        $this->context->cookie->__set(
                            $cookieName,
                            true
                        );
                        $this->post->content = EverPsBlogPost::changeShortcodes(
                            (string) $this->post->content,
                            (int) Context::getContext()->customer->id
                        );
                    }
                } else {
                    $this->post->password_protected = true;
                    $this->post->content = $this->l('This post is password protected');
                }
            } else {
                // Prepare shortcodes
                $this->post->content = EverPsBlogPost::changeShortcodes(
                    (string) $this->post->content,
                    (int) Context::getContext()->customer->id
                );
            }
            $this->post->title = EverPsBlogPost::changeShortcodes(
                (string) $this->post->title,
                (int) Context::getContext()->customer->id
            );
            $this->post->date_add = date('d-m-Y', strtotime($this->post->date_add));
            Hook::exec('actionBeforeEverPostInitContent', array(
                'blog_post' => $this->post,
                'blog_tags' => $tags,
                'blog_products' => $ps_products,
                'blog_author' => $this->author
            ));
            $social_share_links = [];
            $social_share_links['facebook'] = [
                'label' => $this->l('Share'),
                'class' => 'facebook',
                'url' => 'https://www.facebook.com/sharer.php?u='.$page['canonical'],
            ];
            $social_share_links['twitter'] = [
                'label' => $this->l('Tweet'),
                'class' => 'twitter',
                'url' => 'https://twitter.com/intent/tweet?text='.$this->post->title.' '.$page['canonical'],
            ];
            $file_url = EverPsBlogImage::getBlogImageUrl(
                (int) $this->post->id,
                (int) $this->context->shop->id,
                'post'
            );
            $this->context->smarty->assign(
                array(
                    'show_author' => (bool)Configuration::get('EVERBLOG_SHOW_AUTHOR'),
                    'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                    'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
                    'featured_image' => $file_url,
                    'author_cover' => $this->author_cover,
                    'author' => $this->author,
                    'social_share_links' => $social_share_links,
                    'count_products' => $count_products,
                    'post' => $this->post,
                    'tags' => $tags,
                    'ps_products' => $ps_products,
                    'default_lang' => (int) $this->context->language->id,
                    'id_lang' => (int) $this->context->language->id,
                    'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__.'modules/everpsblog/views/img/',
                    'allow_comments' => $this->allow_comments,
                    'animated' => (bool) $animate,
                    'logged' => (bool) $this->context->customer->isLogged(),
                    'comments' => (array)$comments,
                    'commentsCount' => (int) $commentsCount,
                    'allow_views_count' => (bool)Configuration::get('EVERBLOG_SHOW_POST_COUNT'),
                    'only_logged_comment' => (bool)Configuration::get('EVERBLOG_ONLY_LOGGED_COMMENT'),
                )
            );
            $this->setTemplate('module:everpsblog/views/templates/front/post.tpl');
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_POST_LAYOUT');
    }

    public function getBreadcrumbLinks()
    {
        $this->post = new EverPsBlogPost(
            (int)Tools::getValue('id_ever_post'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->l('Blog'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'blog'
            ),
        );
        if ((int) $this->post->id_default_category > 1) {
            $parent_category = new EverPsBlogCategory(
                (int) $this->post->id_default_category,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $breadcrumb['links'][] = array(
                'title' => $parent_category->title,
                'url' => $this->context->link->getModuleLink(
                    'everpsblog',
                    'category',
                    array(
                        'id_ever_category' => $parent_category->id,
                        'link_rewrite' => $parent_category->link_rewrite
                    )
                ),
            );
            if ((bool) $parent_category->hasChildren() === true) {
                $children_categories = EverPsBlogCategory::getChildrenCategories(
                    (int) $this->post->id_default_category,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                foreach ($children_categories as $cat) {
                    if ((bool) $cat->is_root_category === false
                        && (int) $cat->id > 0
                        && !empty($cat->title)
                        && (bool) $cat->active === true
                        && in_array($cat->id, $this->post_categories)
                    ) {
                        $breadcrumb['links'][] = array(
                            'title' => $cat->title,
                            'url' => $this->context->link->getModuleLink(
                                'everpsblog',
                                'category',
                                array(
                                    'id_ever_category' => $cat->id,
                                    'link_rewrite' => $cat->link_rewrite
                                )
                            ),
                        );
                    }
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
            (int) $id_ever_comment
        );

        $mailDir = _PS_MODULE_DIR_ . 'everpsblog/mails/';
        $everShopEmail = Configuration::get('PS_SHOP_EMAIL');
        $mail = Mail::send(
            (int) $this->context->language->id,
            'everpsblog',
            $this->l('A new comment is pending'),
            array(
                '{shop_name}'=>Configuration::get('PS_SHOP_NAME'),
                '{shop_logo}'=>_PS_IMG_DIR_.Configuration::get(
                    'PS_LOGO',
                    null,
                    null,
                    (int) $this->context->shop->id
                ),
                '{comment}' => (string) $comment->comment,
                '{email}' => (string) $comment->user_email,
            ),
            (string) $employee->email,
            null,
            (string) $everShopEmail,
            Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            $mailDir,
            false,
            null,
            (string) $everShopEmail,
            (string) $everShopEmail,
            Configuration::get('PS_SHOP_NAME')
        );
        return $mail;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        $page['body_classes']['page-everblog-post'] = true;
        $page['body_classes']['page-everblog-post-id-'.(int) $this->post->id] = true;
        if ((bool)Context::getContext()->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-'.Configuration::get('EVERPSBLOG_POST_LAYOUT')] = true;
        return $page;
    }
}
