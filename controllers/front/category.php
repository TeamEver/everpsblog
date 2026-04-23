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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlogcategoryModuleFrontController extends AbstractFrontController
{
    use FrontBlogDataProviderTrait;

    protected $author;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $nbr_blogs;
    public $controller_name = 'category';

    public function init()
    {
        $this->category = $this->getFrontCategory(
            (int) Tools::getValue('id_ever_category'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        if (isset($this->category->allowed_groups) && $this->category->allowed_groups) {
            if (is_array($this->category->allowed_groups)) {
                $allowedGroups = [];
            } else {
                $allowedGroups = json_decode($this->category->allowed_groups);
            }
            $customerGroups = Customer::getGroupsStatic(
                (int) $this->context->customer->id
            );
            if (isset($customerGroups)
                && !empty($allowedGroups)
                && !array_intersect($allowedGroups, $customerGroups)
            ) {
                Tools::redirect('index.php?controller=404');
            }
        }
        $this->incrementFrontTaxonomyCount('ever_blog_category', 'id_ever_category', (int) $this->category->id);
        parent::init();
        $this->parent_categories = $this->getBlogTaxonomyService()->getCategoryParentsTaxonomy(
            (int) $this->category->id
        ) ?: [];
        // if inactive category or unexists, redirect
        if (empty($this->category->id)
            || !$this->category->active
            || $this->category->is_root_category
        ) {
            Tools::redirect('index.php?controller=404');
        }
    }


    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('id_ever_category')) {
            $this->assignHreflangLinks('category', $this->getLocalizedParamsByLang(
                'ever_blog_category_lang',
                'id_ever_category',
                (int) $this->category->id
            ));
            $this->post_number = $this->countFrontPostsByCategory(
                (int) Tools::getValue('id_ever_category'),
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );

            $sortOrders = $this->getBlogSortOrderService()->getSortOrders();
            $sortSelected = array_filter($sortOrders, function ($sortOrder) { return $sortOrder['current']; });
            $sortSelected = $sortSelected ? $sortSelected[array_key_first($sortSelected)] : null;
            
            // Pagination only if there is still some posts
            $pagination = $this->getTemplateVarPagination(
                $this->post_number
            );
            // end pagination
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->category->indexable) {
                $seo_index = 'index';
            } else {
                $seo_index = 'noindex';
            }
            if ($this->category->follow) {
                $seo_follow = 'follow';
            } else {
                $seo_follow = 'nofollow';
            }
            $page = $this->context->controller->getTemplateVarPage();
            // SEO opti on pagination; thx FoP ! Awesome channel !
            $page['meta']['robots'] = $seo_index . ', ' . $seo_follow;
            if (Tools::getValue('page')) {
                $meta_title = $this->transShop('Page : ') . Tools::getValue('page') . ' | ' . $this->category->meta_title;
                $meta_description = $this->transShop('Page : ') . Tools::getValue('page') . ' | ' . $this->category->meta_description;
            } else {
                $meta_title = $this->category->meta_title;
                $meta_description = $this->category->meta_description;
            }
            $page['meta']['title'] = $meta_title;
            $page['meta']['description'] = $meta_description;
            $this->context->smarty->assign('page', $page);
            $posts = $this->getFrontPostsByCategory(
                (int) $this->context->language->id,
                (int) $this->context->shop->id,
                (int) $this->category->id,
                (int) $pagination['items_shown_from'] - 1
            );
            if ($this->frontCategoryHasChildren((int) $this->category->id)) {
                $children_categories = $this->getFrontChildrenCategories(
                    (int) $this->category->id,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
            } else {
                $children_categories = false;
            }
            $this->category->content = $this->renderQcdBuilderField(
                'everpsblog_category',
                (int) $this->category->id,
                'content',
                (string) $this->category->content
            );
            $this->category->bottom_content = $this->renderQcdBuilderField(
                'everpsblog_category',
                (int) $this->category->id,
                'bottom_content',
                (string) $this->category->bottom_content
            );
            $postsViewModel = PostViewModel::listFromLegacy($posts);
            $categoryViewModel = TaxonomyViewModel::fromLegacy($this->category, 'category');
            Hook::exec('actionBeforeEverCategoryInitContent', [
                'blog_category' => $this->category,
                'blog_posts' => $posts,
            ]);
            $file_url = $this->getBlogImageService()->getBlogImageUrl(
                (int) $this->category->id,
                (int) $this->context->shop->id,
                'category'
            );
            $feed_url = $this->context->link->getModuleLink(
                $this->module->name,
                'feed',
                [
                    'feed' => 'category',
                    'id_obj' => $this->category->id,
                ],
                true,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $this->context->smarty->assign([
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
                'children_categories' => $children_categories,
                'allow_feed' => (bool) Configuration::get('EVERBLOG_RSS'),
                'feed_url' => $feed_url,
                'featured_image' => $file_url,
                'paginated' => Tools::getValue('page'),
                'post_number' => (int) $this->post_number,
                'pagination' => $pagination,
                'category' => $this->category,
                'category_view' => $categoryViewModel,
                'posts' => $postsViewModel,
                'posts_legacy' => $posts,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => $this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__.'modules/' . $this->module->name . '/views/img/',
                'animated' => $animate,
                'show_featured_post' => true,
                'show_featured_cat' => (bool)Configuration::get('EVERBLOG_SHOW_FEAT_CAT'),
                'sort_orders' => $sortOrders,
                'sort_selected' => $sortSelected ? $sortSelected['label'] : null,
            ]);
            $this->setTemplate('module:' . $this->module->name . '/views/templates/front/category.tpl');
        } else {
            Tools::redirect('index.php?controller=404');
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_CAT_LAYOUT');
    }

    public function getCanonicalURL()
    {
        if (Tools::getValue('page')) {
            return;
        }
        return $this->context->link->getModuleLink(
            $this->module->name,
            'category',
            [
                'id_ever_category' => $this->category->id,
                'link_rewrite' => $this->category->link_rewrite,
            ]
        );
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->transShop('Blog'),
            'url' => $this->context->link->getModuleLink(
                $this->module->name,
                'blog'
            ),
        ];
        foreach ($this->parent_categories as $parent_category) {
            $category = $this->getFrontCategory(
                (int) $parent_category,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            if ((bool) $category->is_root_category === false
                && (int) $category->id > 0
                && !empty($category->title)
                && (bool) $category->active === true
            ) {
                $breadcrumb['links'][] = array(
                    'title' => $category->title,
                    'url' => $this->context->link->getModuleLink(
                        $this->module->name,
                        'category',
                        [
                            'id_ever_category' => $category->id,
                            'link_rewrite' => $category->link_rewrite,
                        ]
                    ),
                );
            }
        }
        $breadcrumb['links'][] = array(
            'title' => $this->category->title,
            'url' => $this->context->link->getModuleLink(
                $this->module->name,
                'category',
                [
                    'id_ever_category' => (int) $this->category->id,
                    'link_rewrite' => $this->category->link_rewrite,
                ]
            ),
        );
        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        $page['body_classes']['page-everblog-category'] = true;
        $page['body_classes']['page-everblog-category-id-' . (int) $this->category->id] = true;
        if ((bool) $this->context->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-' . Configuration::get('EVERPSBLOG_CAT_LAYOUT')] = true;
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
