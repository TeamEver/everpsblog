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
use PrestaShop\Module\Everpsblog\Controller\Front\FrontBlogDataProviderTrait;
use PrestaShop\Module\Everpsblog\ViewModel\Front\PostViewModel;
use PrestaShop\Module\Everpsblog\ViewModel\Front\TaxonomyViewModel;

class EverPsBlogauthorModuleFrontController extends AbstractFrontController
{
    use FrontBlogDataProviderTrait;

    protected $author;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $post_number;
    public $controller_name = 'author';

    public function init()
    {
        $this->module_name = 'everpsblog';
        $this->errors = [];
        $this->author = $this->getFrontAuthor(
            (int) Tools::getValue('id_ever_author'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $customerGroups = Customer::getGroupsStatic(
            (int) $this->context->customer->id
        );
        if (isset($this->author->allowed_groups) && $this->author->allowed_groups) {
            if (is_array($this->author->allowed_groups)) {
                $allowedGroups = [];
            } else {
                $allowedGroups = json_decode($this->author->allowed_groups);
            }
            if (isset($customerGroups)
                && !empty($allowedGroups)
                && !array_intersect($allowedGroups, $customerGroups)
            ) {
                Tools::redirect('index.php?controller=404');
            }
        }
        parent::init();
        // if inactive post or unexists, redirect
        if (!(int) Tools::getValue('id_ever_author')
            || empty($this->author->id)
            || (bool) $this->author->active === false
        ) {
            Tools::redirect('index.php?controller=404');
        }
        $this->incrementFrontTaxonomyCount('ever_blog_author', 'id_ever_author', (int) $this->author->id);
    }


    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('id_ever_author')) {
            $this->assignHreflangLinks('author', $this->getLocalizedParamsByLang(
                'ever_blog_author_lang',
                'id_ever_author',
                (int) $this->author->id
            ));
            $this->post_number = $this->countFrontPostsByAuthor(
                (int) Tools::getValue('id_ever_author'),
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            // Pagination only if there is still some posts
            $pagination = $this->getTemplateVarPagination(
                $this->post_number
            );
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->author->indexable) {
                $seo_index = 'index';
            } else {
                $seo_index = 'noindex';
            }
            if ($this->author->follow) {
                $seo_follow = 'follow';
            } else {
                $seo_follow = 'nofollow';
            }
            $page = $this->context->controller->getTemplateVarPage();
            $page['meta']['robots'] = $seo_index . ', ' . $seo_follow;
            if (Tools::getValue('page')) {
                $meta_title = $this->transShop('Page : ') . Tools::getValue('page') . ' | ' . $this->author->meta_title;
                $meta_description = $this->transShop('Page : ') . Tools::getValue('page') . ' | ' . $this->author->meta_description;
            } else {
                $meta_title = $this->author->meta_title;
                $meta_description = $this->author->meta_description;
            }
            $page['meta']['title'] = $this->author->meta_title;
            $page['meta']['description'] = $this->author->meta_description;
            $this->context->smarty->assign('page', $page);
            // Now prepare template and show it
            // Prepare shortcodes
            $this->author->content = $this->renderQcdBuilderField(
                'everpsblog_author',
                (int) $this->author->id,
                'content',
                (string) $this->author->content
            );
            $this->author->bottom_content = $this->renderQcdBuilderField(
                'everpsblog_author',
                (int) $this->author->id,
                'bottom_content',
                (string) $this->author->bottom_content
            );
            $this->author->nickhandle = 
                $this->author->nickhandle;
            $posts = $this->getFrontPostsByAuthor(
                (int) $this->context->language->id,
                (int) $this->context->shop->id,
                (int) $this->author->id,
                (int) $pagination['items_shown_from'] - 1
            );
            $postsViewModel = PostViewModel::listFromLegacy($posts);
            $authorViewModel = TaxonomyViewModel::fromLegacy($this->author, 'author');
            $authorImage = $this->getBlogImageService()->getBlogImage(
                (int) $this->author->id,
                (int) $this->context->shop->id,
                'author'
            );
            $hasAuthorImage = \Validate::isLoadedObject($authorImage);
            $authorImageUrl = $hasAuthorImage ? $this->getBlogImageService()->getBlogImageUrl(
                (int) $this->author->id,
                (int) $this->context->shop->id,
                'author'
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
                'twitter' => 'X / Twitter',
            ] as $network => $label) {
                if (!empty($this->author->{$network})) {
                    $authorSocialLinks[] = [
                        'network' => $network,
                        'label' => $label,
                        'url' => (string) $this->author->{$network},
                    ];
                }
            }
            $linkedProductViewData = $this->getFrontLinkedProductViewData(
                $this->author->author_products ?? [],
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            Hook::exec('actionBeforeEverAuthorInitContent', [
                'blog_author' => $this->author,
                'blog_products' => $linkedProductViewData['ps_products'],
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
                'url' => 'https://twitter.com/intent/tweet?text=' . $this->author->nickhandle . ' ' . $page['canonical'],
            ];
            $file_url = $this->getBlogImageService()->getBlogImageUrl(
                (int) $this->author->id,
                (int) $this->context->shop->id,
                'author'
            );
            $authorBannerImage = $this->getBlogImageService()->getBlogImage(
                (int) $this->author->id,
                (int) $this->context->shop->id,
                'author_banner'
            );
            $hasAuthorBanner = \Validate::isLoadedObject($authorBannerImage);
            $authorBannerUrl = $hasAuthorBanner ? $this->getBlogImageService()->getBlogImageUrl(
                (int) $this->author->id,
                (int) $this->context->shop->id,
                'author_banner'
            ) : '';
            $feed_url = $this->context->link->getModuleLink(
                $this->module_name,
                'feed',
                [
                    'feed' => 'author',
                    'id_obj' => $this->author->id,
                ],
                true,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $this->context->smarty->assign(array_merge([
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
                'allow_feed' => (bool) Configuration::get('EVERBLOG_RSS'),
                'feed_url' => $feed_url,
                'featured_image' => $file_url,
                'author_banner_image' => $authorBannerUrl,
                'has_author_banner' => $hasAuthorBanner,
                'show_featured_post' => true,
                'posts' => $postsViewModel,
                'posts_legacy' => $posts,
                'author_cover' => $authorImageUrl,
                'has_author_image' => $hasAuthorImage,
                'author_summary' => $authorSummary,
                'author_social_links' => $authorSocialLinks,
                'show_author_intro' => $hasAuthorImage || '' !== trim($authorSummary) || !empty($authorSocialLinks),
                'paginated' => Tools::getValue('page'),
                'post_number' => (int) $this->post_number,
                'pagination' => $pagination,
                'social_share_links' => $social_share_links,
                'author' => $this->author,
                'author_view' => $authorViewModel,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => (int) $this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/everpsblog/views/img/',
                'animated' => (bool) $animate,
                'logged' => (bool) $this->context->customer->isLogged(),
                'linked_products_block_id' => 'author-' . (int) $this->author->id,
            ], $linkedProductViewData));
            $this->setTemplate('module:everpsblog/views/templates/front/author.tpl');
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_AUTHOR_LAYOUT');
    }

    public function getBreadcrumbLinks()
    {
        $this->author = $this->getFrontAuthor(
            (int) Tools::getValue('id_ever_author'),
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
        $breadcrumb['links'][] = [
            'title' => 
                $this->author->nickhandle,
            'url' => $this->context->link->getModuleLink(
                $this->module->name,
                'author',
                [
                    'id_ever_author' => $this->author->id,
                    'link_rewrite' => $this->author->link_rewrite,
                ]
            ),
        ];
        return $breadcrumb;
    }

    public function getCanonicalURL()
    {
        if (Tools::getValue('page')) {
            return;
        }
        return $this->context->link->getModuleLink(
            'everpsblog',
            'author',
            [
                'id_ever_author' => $this->author->id,
                'link_rewrite' => $this->author->link_rewrite,
            ]
        );
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        $page['body_classes']['page-everblog-author'] = true;
        $page['body_classes']['page-everblog-author-id-' . (int) $this->author->id] = true;
        if ((bool) $this->context->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-' . Configuration::get('EVERPSBLOG_AUTHOR_LAYOUT')] = true;
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
