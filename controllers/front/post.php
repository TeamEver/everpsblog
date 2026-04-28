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

use PrestaShop\Module\Everpsblog\Controller\Front\AbstractFrontController;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheTags;
use PrestaShop\Module\Everpsblog\ViewModel\Front\PostViewModel;

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlogpostModuleFrontController extends AbstractFrontController
{
    /** @var \stdClass */
    protected $category;
    /** @var \stdClass */
    protected $tag;
    /** @var \stdClass */
    protected $post;
    /** @var \stdClass */
    protected $blog;
    /** @var \stdClass */
    protected $author;
    protected $author_cover;
    protected $authorHasImage = false;
    /** @var \stdClass */
    protected $default_category;
    /** @var string */
    protected $module_name = '';
    /** @var string[] */
    protected $ip_banned = [];
    /** @var string[] */
    protected $users_banned = [];
    /** @var bool */
    protected $allow_comments = false;
    /** @var array<int, int|string> */
    protected $post_tags = [];
    /** @var array<int, int|string> */
    protected $post_categories = [];
    /** @var array<int, int|string> */
    protected $post_products = [];
    public $controller_name = 'post';

    public function init()
    {
        $defaultAuthorName = Configuration::get('EVERBLOG_DEFAULT_AUTHOR_NAME');
        if (!$defaultAuthorName) {
            $defaultAuthorName = Configuration::get('PS_SHOP_NAME');
        }
        $this->module_name = 'everpsblog';
        $this->ip_banned = explode(',', Configuration::get('EVERBLOG_BANNED_IP'));
        $this->users_banned = explode(',', Configuration::get('EVERBLOG_BANNED_USERS'));
        if (in_array($_SERVER['REMOTE_ADDR'], $this->ip_banned)) {
            $this->allow_comments = false;
        } else {
            $this->allow_comments = (bool) Configuration::get('EVERBLOG_ALLOW_COMMENTS');
        }
        $this->errors = [];
        $this->post = $this->getPostForFront(
            (int) Tools::getValue('id_ever_post'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        if (empty($this->post->id)) {
            Tools::redirect('index.php?controller=404');
        }
        $customerGroups = Customer::getGroupsStatic(
            (int) $this->context->customer->id
        );
        $defaultCategory = $this->getCategoryForFront(
            (int) $this->post->id_default_category,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        if (isset($defaultCategory->allowed_groups) && $defaultCategory->allowed_groups) {
            if (is_array($defaultCategory->allowed_groups)) {
                $allowedGroups = [];
            } else {
                $allowedGroups = json_decode($defaultCategory->allowed_groups);
            }
            if (isset($customerGroups)
                && !empty($allowedGroups)
                && !array_intersect($allowedGroups, $customerGroups)
            ) {
                Tools::redirect('index.php?controller=404');
            }
        }
        $this->default_category = $defaultCategory;
        if (isset($this->post->allowed_groups) && $this->post->allowed_groups) {
            if (is_array($this->post->allowed_groups)) {
                $allowedGroups = [];
            } else {
                $allowedGroups = json_decode($this->post->allowed_groups);
            }
            if (isset($customerGroups)
                && !empty($allowedGroups)
                && !array_intersect($allowedGroups, $customerGroups)
            ) {
                Tools::redirect('index.php?controller=404');
            }
        }
        if (isset($this->post->id_author) && (int) $this->post->id_author > 0) {
            $this->author = $this->getAuthorForFront(
                (int) $this->post->id_author,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            // Hide author depending on customer groups
            $groupAllowed = true;
                if (isset($this->author->allowed_groups)
                    && $this->author->allowed_groups
                ) {
                if (is_array($this->author->allowed_groups)) {
                    $allowedGroups = [];
                } else {
                    $allowedGroups = json_decode($this->author->allowed_groups);
                }
                if (isset($customerGroups)
                    && !empty($allowedGroups)
                    && !array_intersect($allowedGroups, $customerGroups)
                ) {
                    $groupAllowed = false;
                }
            }
            if (!empty($this->author->id) && (bool) $this->author->active === true && (bool) $groupAllowed === true) {
                $this->author->url = $this->context->link->getModuleLink(
                    'everpsblog',
                    'author',
                    [
                        'id_ever_author' => $this->author->id,
                        'link_rewrite' => $this->author->link_rewrite,
                    ]
                );
            } else {
                $this->author = new stdClass();
                $this->author->id_ever_author = 0;
                $this->author->id = 0;
                $this->author->nickhandle = $defaultAuthorName;
                $this->author->url = $this->context->link->getModuleLink(
                    'everpsblog',
                    'blog'
                );
            }
        } else {
            $this->author = new stdClass();
            $this->author->id_ever_author = 0;
            $this->author->id = 0;
            $this->author->nickhandle = $defaultAuthorName;
            $this->author->url = $this->context->link->getModuleLink(
                'everpsblog',
                'blog'
            );
        }
        $authorImage = false;
        $this->author_cover = '';
        if ((int) $this->author->id > 0) {
            $authorImage = $this->getBlogImageService()->getBlogImage(
                (int) $this->author->id,
                (int) $this->context->shop->id,
                'author'
            );
            $this->authorHasImage = Validate::isLoadedObject($authorImage);
            if ($this->authorHasImage) {
                $this->author_cover = $this->getBlogImageService()->getBlogImageUrl(
                    (int) $this->author->id,
                    (int) $this->context->shop->id,
                    'author'
                );
            }
        }
        $this->post_tags = $this->getBlogTaxonomyService()->getPostTagsTaxonomies(
            (int) $this->post->id
        );
        $this->post_categories = $this->getBlogTaxonomyService()->getPostCategoriesTaxonomies(
            (int) $this->post->id
        );
        $this->post_products = $this->getBlogTaxonomyService()->getPostProductsTaxonomies(
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
            if ((bool) Tools::isSubmit('everpostcomment') === false && !$this->isAdmin()) {
                $this->updatePostViewCount(
                    (int) $this->post->id,
                    (int) $this->context->shop->id
                );
            }
        }
    }


    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('id_ever_post')) {
            $this->assignHreflangLinks('post', $this->getLocalizedParamsByLang(
                'ever_blog_post_lang',
                'id_ever_post',
                (int) $this->post->id
            ));
            $errors = [];
            $success = [];
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->post->indexable) {
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
                    $errors[] = $this->transShop(
                        'Wow ! What have you done ? You\'re banned from this blog !'
                    );
                }
                // So now, u're unlogged ? Right, email is required, must be unbanned
                if (!(bool) $this->context->customer->isLogged()) {
                    if (!Tools::getValue('customerEmail')
                        || !Validate::isEmail(Tools::getValue('customerEmail'))
                    ) {
                        $errors[] = $this->transShop('Error : The field "Email" is not valid');
                    } else {
                        if (in_array(Tools::getValue('customerEmail'), $this->users_banned)) {
                            $errors[] = $this->transShop(
                                'Wow ! What have you done ? You\'re banned from this blog !'
                            );
                        }
                    }
                    if (!Tools::getValue('name')
                        || !Validate::isCleanHtml(Tools::getValue('name'))
                    ) {
                        $errors[] = $this->transShop('Error : The field "name" is not valid');
                    }
                }
                if (!Tools::getValue('RgpdCompliance')
                    || !Validate::isBool(Tools::getValue('RgpdCompliance'))
                ) {
                    $errors[] = $this->transShop('Error : The field "RGPD" is not valid');
                }
                if (!Tools::getValue('evercomment')
                    || !Validate::isCleanHtml(Tools::getValue('evercomment'))
                ) {
                    $errors[] = $this->transShop('Error : The field "comments" is not valid');
                }
                $latest = $this->getLatestCommentByEmail(
                    Tools::getValue('customerEmail'),
                    (int) $this->context->language->id
                );
                // Safety before : don't allow comment before specific time
                if (isset($latest->date_add)
                    && $latest->date_add
                    && strtotime($latest->date_add) >= strtotime('-30 minutes')
                ) {
                    $errors[] = $this->transShop('You must wait before sending another comment');
                }
                if (count($errors)) {
                    $this->context->smarty->assign(['errors' => $errors]);
                } else {
                    $comment = new stdClass();
                    $comment->id_ever_post = (int) $this->post->id;
                    $comment->id_lang = (int) $this->context->language->id;
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
                            (int)$this->context->customer->id
                        );
                        $comment->user_email = $customer->email;
                        $comment->name = $customer->firstname;
                    }
                    $comment->comment = Tools::getValue('evercomment');
                    $comment->active = 0;
                    $comment->id = $this->addComment($comment);
                    $comment->id_ever_comment = $comment->id;
                    // alert admin ! comment saved ! whouhouhouhou !
                    if ($this->sendCommentAlert((int) $comment->id)) {
                        $success[] = $this->transShop('Your comment has been submitted');
                        $this->context->smarty->assign([
                            'successes' => $success,
                        ]);
                    } else {
                        $errors[] = $this->transShop('Email has not been sent to admin');
                        $this->context->smarty->assign([
                            'errors' => $errors,
                        ]);
                    }
                }
            }
            // Now prepare template and show it
            $ps_products = [];
            if (!empty($this->post_products)) {
                $showPrice = true;
                $assembler = new ProductAssembler($this->context);
                $presenterFactory = new ProductPresenterFactory($this->context);
                $presentationSettings = $presenterFactory->getPresentationSettings();
                $presenter = new ProductListingPresenter(
                    new ImageRetriever(
                       $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                   $this->context->getTranslator()
                );
                $presentationSettings->showPrices = $showPrice;
                foreach ($this->post_products as $productId) {
                    $pproduct = new Product(
                        (int) $productId,
                        true,
                        (int) $this->context->language->id,
                        (int) $this->context->shop->id
                    );
                    if (Product::checkAccessStatic((int) $pproduct->id, false)) {
                        $pproduct_cover = Product::getCover(
                            (int) $pproduct->id
                        );
                        if (is_array($pproduct_cover) && isset($pproduct_cover['id_image'])) {
                            $pproduct->cover = (int) $pproduct_cover['id_image'];
                        }
                        $ps_products[] = $presenter->present(
                            $presentationSettings,
                            $assembler->assembleProduct(['id_product' => $pproduct->id]),
                           $this->context->language
                        );
                    }
                }
            }
            $count_products = count($ps_products);
            $ps_products_chunks = $count_products > 0 ? array_chunk($ps_products, 4) : [];
            $tags = [];
            if (!empty($this->post_tags)) {
                foreach ($this->post_tags as $postTagId) {
                    $current_post_tag = $this->getTagForFront(
                        (int) $postTagId,
                        (int) $this->context->language->id,
                        (int) $this->context->shop->id
                    );
                    if (isset($current_post_tag->allowed_groups)
                        && $current_post_tag->allowed_groups
                    ) {
                        $allowedGroups = json_decode($current_post_tag->allowed_groups);
                        $customerGroups = Customer::getGroupsStatic(
                            (int) $this->context->customer->id
                        );
                        if (isset($customerGroups)
                            && !empty($allowedGroups)
                            && !array_intersect($allowedGroups, $customerGroups)
                        ) {
                            continue;
                        }
                    }
                    if (!empty($current_post_tag->id) && (bool) $current_post_tag->active === true) {
                        $tags[] = $current_post_tag;
                    }
                }
            }
            $commentsCount = $this->getCommentsCount(
                (int) $this->post->id,
                (int) $this->context->language->id
            );
            $comments = $this->getCommentsByPost(
                (int) $this->post->id,
                (int) $this->context->language->id
            );
            $related_posts = [];
            if ((bool) Configuration::get('EVERBLOG_SHOW_RELATED_POSTS') === true) {
                $related_posts = $this->getPostsByCategoryForFront(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    (int) $this->post->id_default_category,
                    0,
                    5
                );
                foreach ($related_posts as $key => $related) {
                    if ((int) $related->id === (int) $this->post->id) {
                        unset($related_posts[$key]);
                    }
                }
                if ($related_posts && count($related_posts) > 4) {
                    $related_posts = array_slice($related_posts, 0, 4);
                }
            }
            // Password protected
            $cookieName = $this->context->shop->id . $this->post->id . Tools::encrypt('everpsblog/post-' . $this->post->id);
            if ($this->post->psswd
                && !empty($this->post->psswd)
                && !$this->context->cookie->__isset($cookieName)
            ) {
                if (Tools::getValue('post_psswd')) {
                    if ($this->checkPostPassword($this->post->id, md5(_COOKIE_KEY_ . Tools::getValue('post_psswd'))) === false) {
                        $this->post->password_protected = true;
                        $this->post->content = $this->transShop('This post is password protected');
                    }
                    if ($this->checkPostPassword($this->post->id, md5(_COOKIE_KEY_ . Tools::getValue('post_psswd'))) === true) {
                        $this->context->cookie->__set(
                            $cookieName,
                            true
                        );
                        $this->post->content = $this->renderQcdBuilderField(
                            'everpsblog_post',
                            (int) $this->post->id,
                            'content',
                            (string) $this->post->content
                        );
                    }
                } else {
                    $this->post->password_protected = true;
                    $this->post->content = $this->transShop('This post is password protected');
                }
            } else {
                // Render builder content
                $this->post->content = $this->renderQcdBuilderField(
                    'everpsblog_post',
                    (int) $this->post->id,
                    'content',
                    (string) $this->post->content
                );
            }
            $this->post->title = 
                $this->post->title;
            $this->post->date_add = date('d-m-Y', strtotime($this->post->date_add));
            Hook::exec('actionBeforeEverPostInitContent', [
                'blog_post' => $this->post,
                'blog_tags' => $tags,
                'blog_products' => $ps_products,
                'blog_author' => $this->author,
            ]);
            $social_share_links = [];
            $social_share_links['facebook'] = [
                'label' => $this->transShop('Share'),
                'class' => 'facebook',
                'url' => 'https://www.facebook.com/sharer.php?u=' . $page['canonical'],
            ];
            $social_share_links['twitter'] = [
                'label' => $this->transShop('Share on X'),
                'class' => 'x',
                'url' => 'https://twitter.com/intent/tweet?text=' . $this->post->title . ' ' . $page['canonical'],
            ];
            $postImage = $this->getBlogImageService()->getBlogImage(
                (int) $this->post->id,
                (int) $this->context->shop->id,
                'post'
            );
            $postBannerImage = $this->getBlogImageService()->getBlogImage(
                (int) $this->post->id,
                (int) $this->context->shop->id,
                'post_banner'
            );
            $showFeaturedPost = (bool) Configuration::get('EVERBLOG_SHOW_FEAT_POST')
                && Validate::isLoadedObject($postImage);
            $file_url = $this->getBlogImageService()->getBlogImageUrl(
                (int) $this->post->id,
                (int) $this->context->shop->id,
                'post'
            );
            $hasPostBanner = Validate::isLoadedObject($postBannerImage);
            $postBannerUrl = $hasPostBanner ? $this->getBlogImageService()->getBlogImageUrl(
                (int) $this->post->id,
                (int) $this->context->shop->id,
                'post_banner'
            ) : '';
            $authorSummary = '';
            if (!empty($this->author->excerpt)) {
                $authorSummary = (string) $this->author->excerpt;
            } elseif (!empty($this->author->meta_description)) {
                $authorSummary = (string) $this->author->meta_description;
            }
            $authorSocialLinks = [];
            foreach ([
                'facebook' => 'Facebook',
                'linkedin' => 'LinkedIn',
                'twitter' => 'X',
            ] as $network => $label) {
                if (!empty($this->author->{$network})) {
                    $authorSocialLinks[] = [
                        'network' => $network === 'twitter' ? 'x' : $network,
                        'label' => $label,
                        'url' => (string) $this->author->{$network},
                    ];
                }
            }
            $postViewModel = PostViewModel::fromLegacy($this->post);
            $showAiSummaryBanner = Configuration::get('EVERBLOG_SHOW_AI_SUMMARY_BANNER');
            if (false === $showAiSummaryBanner) {
                $showAiSummaryBanner = true;
            }
            $this->context->smarty->assign([
                'show_author' => (bool) Configuration::get('EVERBLOG_SHOW_AUTHOR'),
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
                'featured_image' => $file_url,
                'post_banner_image' => $postBannerUrl,
                'has_post_banner' => $hasPostBanner,
                'show_featured_post' => $showFeaturedPost,
                'author_cover' => $this->author_cover,
                'has_author_image' => $this->authorHasImage,
                'show_post_author_box' => (int) $this->author->id > 0,
                'author_summary' => $authorSummary,
                'author_social_links' => $authorSocialLinks,
                'author' => $this->author,
                'default_category' => $this->default_category,
                'social_share_links' => $social_share_links,
                'count_products' => $count_products,
                'post' => $this->post,
                'post_view' => $postViewModel,
                'tags' => $tags,
                'ps_products' => $ps_products,
                'ps_products_chunks' => $ps_products_chunks,
                'related_posts' => $related_posts,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => (int) $this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/everpsblog/views/img/',
                'allow_comments' => $this->allow_comments,
                'animated' => (bool) $animate,
                'logged' => (bool) $this->context->customer->isLogged(),
                'comments' => (array) $comments,
                'commentsCount' => (int) $commentsCount,
                'allow_views_count' => (bool) Configuration::get('EVERBLOG_SHOW_POST_COUNT'),
                'show_post_tags' => (bool) Configuration::get('EVERBLOG_SHOW_POST_TAGS'),
                'show_ai_summary_banner' => (bool) $showAiSummaryBanner,
                'only_logged_comment' => (bool) Configuration::get('EVERBLOG_ONLY_LOGGED_COMMENT'),
            ]);
            $this->setTemplate($this->getFrontThemeTemplatePath('post.tpl'));
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_POST_LAYOUT');
    }

    public function getBreadcrumbLinks()
    {
        $this->post = $this->getPostForFront(
            (int) Tools::getValue('id_ever_post'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->transShop('Blog'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'blog'
            ),
        ];
        $defaultCategoryId = (int) ($this->post->id_default_category ?? 0);
        if ($defaultCategoryId > 0) {
            $breadcrumbCategoryIds = $this->getBlogTaxonomyService()->getCategoryParentsTaxonomy($defaultCategoryId);
            $breadcrumbCategoryIds[] = $defaultCategoryId;
            $breadcrumbCategoryIds = array_values(array_unique(array_map('intval', $breadcrumbCategoryIds)));

            foreach ($breadcrumbCategoryIds as $categoryId) {
                $category = $this->getCategoryForFront(
                    (int) $categoryId,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );

                if (!empty($category->is_root_category)
                    || (int) ($category->id ?? 0) <= 0
                    || empty($category->title)
                    || (bool) ($category->active ?? false) !== true
                ) {
                    continue;
                }

                $breadcrumb['links'][] = [
                    'title' => $category->title,
                    'url' => $this->context->link->getModuleLink(
                        'everpsblog',
                        'category',
                        [
                            'id_ever_category' => (int) $category->id,
                            'link_rewrite' => $category->link_rewrite,
                        ]
                    ),
                ];
            }
        }
        $breadcrumb['links'][] = [
            'title' => 
                $this->post->title,
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'post',
                [
                    'id_ever_post' => $this->post->id,
                    'link_rewrite' => $this->post->link_rewrite,
                ]
            ),
        ];
        return $breadcrumb;
    }

    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink(
            'everpsblog',
            'post',
            [
                'id_ever_post' => $this->post->id,
                'link_rewrite' => $this->post->link_rewrite,
            ]
        );
    }

    private function getPostForFront($idPost, $idLang, $idShop): stdClass
    {
        $sql = new DbQuery();
        $sql->select('p.id_ever_post, p.id_ever_post AS id, p.id_shop, p.id_author, p.id_author AS id_ever_author, p.id_default_category, p.post_status, p.date_add, p.date_upd, p.indexable, p.follow, p.sitemap, p.active, p.allowed_groups, p.post_categories, p.post_tags, p.post_products, p.psswd, p.starred, p.count, p.groups, pl.title AS title, pl.meta_title AS meta_title, pl.meta_description AS meta_description, pl.link_rewrite AS link_rewrite, pl.content AS content, pl.excerpt AS excerpt');
        $sql->from('ever_blog_post', 'p');
        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
        $sql->where('p.id_ever_post = ' . (int) $idPost);

        return $this->arrayToObject(Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
    }

    private function getCategoryForFront($idCategory, $idLang, $idShop): stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idCategory, $idLang, $idShop], function () use ($idCategory, $idLang, $idShop) {
            $sql = new DbQuery();
            $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
            $sql->from('ever_blog_category', 'c');
            $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
            $sql->where('c.id_ever_category = ' . (int) $idCategory);

            return $this->arrayToObject(Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::category((int) $idCategory)]);
    }

    private function getAuthorForFront($idAuthor, $idLang, $idShop): stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idAuthor, $idLang, $idShop], function () use ($idAuthor, $idLang, $idShop) {
            $sql = new DbQuery();
            $excerptSelect = $this->authorExcerptColumnExists() ? 'al.excerpt' : '"" AS excerpt';
            $sql->select('a.*, a.id_ever_author AS id, al.meta_title, al.meta_description, al.link_rewrite, ' . $excerptSelect . ', al.content, al.bottom_content');
            $sql->from('ever_blog_author', 'a');
            $sql->innerJoin('ever_blog_author_lang', 'al', 'al.id_ever_author = a.id_ever_author AND al.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_author_shop', 'ass', 'ass.id_ever_author = a.id_ever_author AND ass.id_shop = ' . (int) $idShop);
            $sql->where('a.id_ever_author = ' . (int) $idAuthor);

            return $this->arrayToObject(Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::author((int) $idAuthor)]);
    }

    private function authorExcerptColumnExists()
    {
        return (bool) $this->frontCacheRemember(__METHOD__, [], function () {
            return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                'DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_author_lang` `excerpt`'
            );
        });
    }

    private function getTagForFront($idTag, $idLang, $idShop): stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idTag, $idLang, $idShop], function () use ($idTag, $idLang, $idShop) {
            $sql = new DbQuery();
            $sql->select('t.*, t.id_ever_tag AS id, tl.title, tl.meta_title, tl.meta_description, tl.link_rewrite, tl.content, tl.bottom_content');
            $sql->from('ever_blog_tag', 't');
            $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $idShop);
            $sql->where('t.id_ever_tag = ' . (int) $idTag);

            return $this->arrayToObject(Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::tag((int) $idTag)]);
    }

    /**
     * @return list<stdClass>
     */
    private function getPostsByCategoryForFront($idLang, $idShop, $idCategory, $start = 0, $limit = 5): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop, $idCategory, $start, $limit], function () use ($idLang, $idShop, $idCategory, $start, $limit) {
            $sql = new DbQuery();
            $sql->select('p.id_ever_post, p.id_ever_post AS id, p.id_shop, p.id_author, p.id_author AS id_ever_author, p.id_default_category, p.post_status, p.date_add, p.date_upd, p.indexable, p.follow, p.sitemap, p.active, p.allowed_groups, p.post_categories, p.post_tags, p.post_products, p.psswd, p.starred, p.count, p.groups, pl.title AS title, pl.meta_title AS meta_title, pl.meta_description AS meta_description, pl.link_rewrite AS link_rewrite, pl.content AS content, pl.excerpt AS excerpt');
            $sql->from('ever_blog_post', 'p');
            $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
            $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
            $sql->where('pc.id_ever_post_category = ' . (int) $idCategory);
            $sql->where('p.post_status = "published"');
            $sql->orderBy('p.date_add DESC, p.id_ever_post DESC');
            $sql->limit((int) $limit, (int) $start);

            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
            foreach ($rows as &$row) {
                $postId = (int) $row['id_ever_post'];
                $row['featured_thumb'] = $this->getBlogImageService()->getBlogThumbUrl($postId, (int) $idShop, 'post');
                $row['featured_image'] = $this->getBlogImageService()->getBlogImageUrl($postId, (int) $idShop, 'post');
                $row['cover'] = $row['featured_thumb'];
            }

            return $this->rowsToObjects($rows);
        }, [BlogFrontCacheTags::category((int) $idCategory)], function ($posts) {
            return $this->frontExtractEntityTags($posts, 'post', ['id', 'id_ever_post']);
        });
    }

    /**
     * @return list<stdClass>
     */
    private function getChildrenCategoriesForFront($idParentCategory, $idLang, $idShop): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idParentCategory, $idLang, $idShop], function () use ($idParentCategory, $idLang, $idShop) {
            $sql = new DbQuery();
            $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
            $sql->from('ever_blog_category', 'c');
            $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
            $sql->where('c.id_parent_category = ' . (int) $idParentCategory);
            $sql->orderBy('cl.title ASC');

            return $this->rowsToObjects(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        }, [BlogFrontCacheTags::category((int) $idParentCategory)], function ($categories) {
            return $this->frontExtractEntityTags($categories, 'category', ['id', 'id_ever_category']);
        });
    }

    private function categoryHasChildren($idCategory)
    {
        return (bool) $this->frontCacheRemember(__METHOD__, [$idCategory], function () use ($idCategory) {
            $sql = new DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('ever_blog_category');
            $sql->where('id_parent_category = ' . (int) $idCategory);

            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        }, [BlogFrontCacheTags::category((int) $idCategory)]);
    }

    private function updatePostViewCount($idPost, $idShop)
    {
        return (bool) Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'ever_blog_post`
            SET `count` = `count` + 1
            WHERE `id_ever_post` = ' . (int) $idPost . '
                AND `id_shop` = ' . (int) $idShop
        );
    }

    private function isAdmin()
    {
        return !empty((new Cookie('psAdmin'))->id_employee);
    }

    private function getLatestCommentByEmail($email, $idLang): stdClass
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('ever_blog_comments');
        $sql->where('user_email = "' . pSQL((string) $email) . '"');
        $sql->where('id_lang = ' . (int) $idLang);
        $sql->orderBy('date_add DESC');
        $sql->limit(1);

        return $this->arrayToObject(Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
    }

    private function addComment($comment)
    {
        $now = date('Y-m-d H:i:s');
        Db::getInstance()->insert('ever_blog_comments', [
            'id_ever_post' => (int) $comment->id_ever_post,
            'id_lang' => (int) $comment->id_lang,
            'comment' => pSQL((string) $comment->comment, true),
            'name' => pSQL((string) $comment->name),
            'user_email' => pSQL((string) $comment->user_email),
            'date_add' => pSQL($now),
            'date_upd' => pSQL($now),
            'active' => 0,
        ]);

        $commentId = (int) Db::getInstance()->Insert_ID();
        $this->getBlogFrontCacheInvalidatorService()->invalidateCommentMutation(
            $commentId,
            (int) ($comment->id_ever_post ?? 0)
        );

        return $commentId;
    }

    private function getCommentsCount($idPost, $idLang)
    {
        return (int) $this->frontCacheRemember(__METHOD__, [$idPost, $idLang], function () use ($idPost, $idLang) {
            $sql = new DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('ever_blog_comments');
            $sql->where('id_ever_post = ' . (int) $idPost);
            $sql->where('id_lang = ' . (int) $idLang);
            $sql->where('active = 1');

            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }, [BlogFrontCacheTags::postComments((int) $idPost)]);
    }

    /**
     * @return list<stdClass>
     */
    private function getCommentsByPost($idPost, $idLang): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idPost, $idLang], function () use ($idPost, $idLang) {
            $sql = new DbQuery();
            $sql->select('*, id_ever_comment AS id');
            $sql->from('ever_blog_comments');
            $sql->where('id_ever_post = ' . (int) $idPost);
            $sql->where('id_lang = ' . (int) $idLang);
            $sql->where('active = 1');
            $sql->orderBy('date_add ASC');

            return $this->rowsToObjects(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        }, [BlogFrontCacheTags::postComments((int) $idPost)], function ($comments) {
            return $this->frontExtractEntityTags($comments, 'comment', ['id', 'id_ever_comment']);
        });
    }

    private function getComment($idComment): stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idComment], function () use ($idComment) {
            $sql = new DbQuery();
            $sql->select('*, id_ever_comment AS id');
            $sql->from('ever_blog_comments');
            $sql->where('id_ever_comment = ' . (int) $idComment);

            return $this->arrayToObject(Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::comment((int) $idComment)]);
    }

    private function checkPostPassword($idPost, $passwordHash)
    {
        return (bool) $this->frontCacheRemember(__METHOD__, [$idPost, (string) $passwordHash], function () use ($idPost, $passwordHash) {
            $sql = new DbQuery();
            $sql->select('id_ever_post');
            $sql->from('ever_blog_post');
            $sql->where('id_ever_post = ' . (int) $idPost);
            $sql->where('psswd = "' . pSQL((string) $passwordHash) . '"');
            $sql->where('post_status = "published"');

            return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }, [BlogFrontCacheTags::post((int) $idPost)]);
    }

    private function arrayToObject($row): stdClass
    {
        if (!is_array($row)) {
            return new stdClass();
        }

        return (object) $row;
    }

    /**
     * @return list<stdClass>
     */
    private function rowsToObjects($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        return array_map(function ($row) {
            return (object) $row;
        }, $rows);
    }

    protected function sendCommentAlert($id_ever_comment)
    {
        $employee = new Employee((int) Configuration::get('EVERBLOG_ADMIN_EMAIL'));
        $comment = $this->getComment((int) $id_ever_comment);

        $mailDir = _PS_MODULE_DIR_ . 'everpsblog/mails/';
        $everShopEmail = Configuration::get('PS_SHOP_EMAIL');
        $mail = Mail::send(
            (int) $this->context->language->id,
            'everpsblog',
            $this->transShop('A new comment is pending'),
            [
                '{shop_name}'=>Configuration::get('PS_SHOP_NAME'),
                '{shop_logo}'=>_PS_IMG_DIR_ . Configuration::get(
                    'PS_LOGO',
                    null,
                    null,
                    (int) $this->context->shop->id
                ),
                '{comment}' => isset($comment->comment) ? $comment->comment : '',
                '{email}' => isset($comment->user_email) ? $comment->user_email : '',
            ],
            $employee->email,
            null,
            $everShopEmail,
            Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            $mailDir,
            false,
            null,
            $everShopEmail,
            $everShopEmail,
            Configuration::get('PS_SHOP_NAME')
        );
        return $mail;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        $page['body_classes']['page-everblog-post'] = true;
        $page['body_classes']['page-everblog-post-id-' . (int) $this->post->id] = true;
        if ((bool) $this->context->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-' . Configuration::get('EVERPSBLOG_POST_LAYOUT')] = true;
        return $page;
    }

    protected function getBlogImageService()
    {
        return parent::getBlogImageService();
    }

    protected function getBlogTaxonomyService()
    {
        return parent::getBlogTaxonomyService();
    }

    protected function getBlogSortOrderService()
    {
        return parent::getBlogSortOrderService();
    }

}
