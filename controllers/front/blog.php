<?php

declare(strict_types=1);

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

class EverPsBlogblogModuleFrontController extends AbstractFrontController
{
    /** @var \stdClass */
    protected $author;
    /** @var \stdClass */
    protected $category;
    /** @var \stdClass */
    protected $tag;
    /** @var \stdClass */
    protected $post;
    /** @var \stdClass */
    protected $blog;
    /** @var string */
    protected $blog_path = '';
    /** @var \Category|null */
    protected $featured_category;
    public $post_number;
    public $controller_name = 'blog';

    private function getPostRowsCount($idLang, $idShop)
    {
        return (int) $this->frontCacheRemember(__METHOD__, [$idLang, $idShop], function () use ($idLang, $idShop) {
            $sql = new DbQuery();
            $sql->select('COUNT(DISTINCT p.id_ever_post)');
            $sql->from('ever_blog_post', 'p');
            $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
            $sql->where('p.post_status = "published"');
            $sql->where('p.active = 1');

            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }, [BlogFrontCacheTags::BLOG_LISTING]);
    }

    private function getPostRows($idLang, $idShop, $start, $limit, $starred = null, $sortBy = 'p.date_add', $sortWay = 'DESC')
    {
        $allowedSortBy = ['p.date_add', 'p.id_ever_post', 'pl.title', 'p.count'];
        $allowedSortWay = ['ASC', 'DESC'];
        $sortBy = in_array($sortBy, $allowedSortBy, true) ? $sortBy : 'p.date_add';
        $sortWay = in_array(strtoupper($sortWay), $allowedSortWay, true) ? strtoupper($sortWay) : 'DESC';
        $baseTags = [BlogFrontCacheTags::BLOG_LISTING];
        if (null !== $starred) {
            $baseTags[] = BlogFrontCacheTags::BLOG_STARRED;
        }

        $excerptLength = max(1, (int) Configuration::get('EVERPSBLOG_EXCERPT'));

        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop, $start, $limit, $starred, $sortBy, $sortWay, $excerptLength], function () use ($idLang, $idShop, $start, $limit, $starred, $sortBy, $sortWay, $excerptLength) {
            $sql = new DbQuery();
            $sql->select('p.id_ever_post, p.id_ever_post AS id, p.id_default_category, p.id_author AS id_ever_author, p.post_status, p.date_add, p.date_upd, p.active, p.starred, p.count, pl.title AS title, pl.link_rewrite AS link_rewrite, pl.meta_title AS meta_title, pl.meta_description AS meta_description, pl.excerpt AS excerpt, pl.content AS content');
            $sql->from('ever_blog_post', 'p');
            $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
            $sql->where('p.post_status = "published"');
            $sql->where('p.active = 1');
            if (null !== $starred) {
                $sql->where('p.starred = ' . (int) $starred);
            }
            $sql->orderBy($sortBy . ' ' . $sortWay . ', p.id_ever_post DESC');
            $sql->limit((int) $limit, (int) $start);

            $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
            foreach ($rows as &$row) {
                $row['url'] = $this->context->link->getModuleLink(
                    $this->module->name,
                    'post',
                    [
                        'id_ever_post' => (int) $row['id_ever_post'],
                        'link_rewrite' => (string) $row['link_rewrite'],
                    ]
                );
                $row['featured_thumb'] = $this->getBlogImageService()->getBlogThumbUrl(
                    (int) $row['id_ever_post'],
                    (int) $idShop,
                    'post'
                );
                $row['featured_image'] = $this->getBlogImageService()->getBlogImageUrl(
                    (int) $row['id_ever_post'],
                    (int) $idShop,
                    'post'
                );
                $row['cover'] = $row['featured_thumb'];
                if (!empty($row['excerpt']) && !$this->isPlaceholderExcerpt((string) $row['excerpt'])) {
                    $row['summary'] = (string) $row['excerpt'];
                    continue;
                }

                $contentSummary = trim(strip_tags((string) $row['content']));
                $row['summary'] = '' !== $contentSummary && !$this->isPlaceholderExcerpt($contentSummary)
                    ? Tools::substr($contentSummary, 0, $excerptLength)
                    : '';
            }

            return $rows;
        }, $baseTags, function ($rows) {
            return $this->frontExtractEntityTags($rows, 'post', ['id', 'id_ever_post']);
        });
    }

    private function isPlaceholderExcerpt(string $excerpt): bool
    {
        $excerpt = html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $excerpt = trim(strip_tags($excerpt));
        $excerpt = preg_replace('/\s+/u', ' ', $excerpt);
        $excerpt = is_string($excerpt) ? trim($excerpt) : '';

        if (function_exists('iconv')) {
            $asciiExcerpt = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $excerpt);
            if (is_string($asciiExcerpt)) {
                $excerpt = $asciiExcerpt;
            }
        }

        $excerpt = strtolower($excerpt);
        $excerpt = preg_replace('/[^a-z0-9]+/', '-', $excerpt);
        $excerpt = is_string($excerpt) ? trim($excerpt, '-') : '';

        return in_array($excerpt, ['resume', 'resume-de-l-article'], true);
    }

    private function getFrontLocalizedCategories($idLang, $idShop)
    {
        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop], function () use ($idLang, $idShop) {
            $sql = new DbQuery();
            $sql->select('c.id_ever_category, c.is_root_category, cl.title, cl.link_rewrite');
            $sql->from('ever_blog_category', 'c');
            $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
            $sql->where('c.active = 1');
            $sql->orderBy('cl.title ASC');

            return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
        }, [BlogFrontCacheTags::BLOG_CATEGORIES_INDEX], function ($categories) {
            return $this->frontExtractEntityTags($categories, 'category', ['id_ever_category', 'id']);
        });
    }

    private function getFrontLocalizedTags($idLang, $idShop)
    {
        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop], function () use ($idLang, $idShop) {
            $sql = new DbQuery();
            $sql->select('t.id_ever_tag, tl.title, tl.link_rewrite');
            $sql->from('ever_blog_tag', 't');
            $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $idShop);
            $sql->where('t.active = 1');
            $sql->orderBy('tl.title ASC');

            return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
        }, [BlogFrontCacheTags::BLOG_TAGS_INDEX], function ($tags) {
            return $this->frontExtractEntityTags($tags, 'tag', ['id_ever_tag', 'id']);
        });
    }

    public function init()
    {
        parent::init();
        $this->blog_path = $this->getFrontThemeAbsolutePath();
    }


    public function initContent()
    {
        parent::initContent();
        $this->assignHreflangLinks('blog');
        $this->post_number = $this->getPostRowsCount(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $this->featured_category = new Category(
            (int) Configuration::get('EVERBLOG_CAT_FEATURED'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $featured_products = $this->featured_category->getProducts(
            (int) $this->context->language->id,
            1,
            (int) Configuration::get('EVERPSBLOG_PAGINATION')
        );
        if (!empty($featured_products)) {
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
            $productsForTemplate = [];
            $presentationSettings->showPrices = $showPrice;
            if (is_array($featured_products)) {
                foreach ($featured_products as $productId) {
                    $productsForTemplate[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct(['id_product' => $productId['id_product']]),
                        $this->context->language
                    );
                }
            }
            $this->context->smarty->assign([
                'everhome_products' => $productsForTemplate,
            ]);
        }    

        $sortOrders = $this->getBlogSortOrderService()->getSortOrders();
        $sortSelected = array_filter($sortOrders, function ($sortOrder) { return $sortOrder['current']; });
        $sortSelected = $sortSelected ? $sortSelected[array_key_first($sortSelected)] : null;
        
        // pagination
        $pagination = $this->getTemplateVarPagination($this->post_number);
        // SEO title and meta desc
        $everblog_title = $this->getModuleConfigInMultipleLangs('EVERBLOG_TITLE');
        $meta_title = $everblog_title[(int) Context::getContext()->language->id];
        $everblog_desc = $this->getModuleConfigInMultipleLangs('EVERBLOG_META_DESC');
        $meta_desc = $everblog_desc[(int) Context::getContext()->language->id];
        $page = $this->context->controller->getTemplateVarPage();
        if (Tools::getValue('page')) {
            $meta_title = $this->transShop('Page : ') . Tools::getValue('page') . ' | ' . $meta_title;
            $meta_description = $this->transShop('Page : ') . Tools::getValue('page') . ' | ' . $meta_desc;
        }
        $page['meta']['title'] = $meta_title;
        $page['meta']['description'] = $meta_desc;
        if (!Tools::getValue('page')) {
            $page['meta']['robots'] = 'index, follow';
        } else {
            $page['meta']['robots'] = 'noindex, follow';
        }
        $this->context->smarty->assign('page', $page);
        $everpsblogposts = $this->getPostRows(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            (int) $pagination['items_shown_from'] - 1,
            (int) Configuration::get('EVERPSBLOG_PAGINATION'),
            null,
            $sortSelected && Validate::isOrderBy($sortSelected['order_by']) ? 'p.' . $sortSelected['order_by'] : 'p.date_add',
            $sortSelected && Validate::isOrderWay($sortSelected['order_way']) ? $sortSelected['order_way'] : 'DESC'
        );
        $postsViewModel = PostViewModel::listFromLegacy($everpsblogposts);
        $starredPosts = $this->getPostRows(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            (int) $pagination['items_shown_from'] - 1,
            (int) Configuration::get('EVERPSBLOG_PAGINATION'),
            true,
            $sortSelected && Validate::isOrderBy($sortSelected['order_by']) ? 'p.' . $sortSelected['order_by'] : 'p.date_add',
            $sortSelected && Validate::isOrderWay($sortSelected['order_way']) ? $sortSelected['order_way'] : null,
        );
        $evercategories = $this->getFrontLocalizedCategories(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $evertags = $this->getFrontLocalizedTags(
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        // Default blog text
        $everblog_top_text = $this->getModuleConfigInMultipleLangs('EVERBLOG_TOP_TEXT');
        $default_blog_top_text = $everblog_top_text[(int) Context::getContext()->language->id];
        $everblog_bottom_text = $this->getModuleConfigInMultipleLangs('EVERBLOG_BOTTOM_TEXT');
        $default_blog_bottom_text = $everblog_bottom_text[(int) Context::getContext()->language->id];
        $default_blog_top_text = $this->renderQcdBuilderField(
            'everpsblog_configuration',
            1,
            'top_text',
            (string) $default_blog_top_text
        );
        $default_blog_bottom_text = $this->renderQcdBuilderField(
            'everpsblog_configuration',
            1,
            'bottom_text',
            (string) $default_blog_bottom_text
        );
        $everblog_main_title = $this->getModuleConfigInMultipleLangs('EVERBLOG_MAIN_TITLE');
        $blog_page_title = (string) ($everblog_main_title[(int) Context::getContext()->language->id] ?? '');
        $everblog_hero_subtitle = $this->getModuleConfigInMultipleLangs('EVERBLOG_HERO_SUBTITLE');
        $blog_page_subtitle = (string) ($everblog_hero_subtitle[(int) Context::getContext()->language->id] ?? '');
        Hook::exec('actionBeforeEverBlogInitContent', [
            'blog_post_number' => &$this->post_number,
            'starred' => &$starredPosts,
            'everpsblog' => &$everpsblogposts,
            'everpsblogcategories' => &$evercategories,
            'blog_page' => Tools::getValue('page'),
        ]);
        $feed_url = $this->context->link->getModuleLink(
            $this->module->name,
            'feed',
            ['feed' => 'blog'],
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $facet_url = $this->context->link->getModuleLink(
            $this->module->name,
            'filter'
        );
        $this->context->smarty->assign([
            'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
            'blog_path' => $this->blog_path,
            'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
            'allow_feed' => (bool) Configuration::get('EVERBLOG_RSS'),
            'feed_url' => $feed_url,
            'default_blog_top_text' => $default_blog_top_text,
            'default_blog_bottom_text' => $default_blog_bottom_text,
            'blog_page_title' => $blog_page_title,
            'blog_page_subtitle' => $blog_page_subtitle,
            'paginated' => Tools::getValue('page'),
            'post_number' => (int) $this->post_number,
            'pagination' => $pagination,
            'everpsblog' => $everpsblogposts,
            'posts' => $postsViewModel,
            'posts_legacy' => $everpsblogposts,
            'starredPosts' => $starredPosts,
            'evercategory' => $evercategories,
            'evertags' => $evertags,
            'default_lang' => (int) $this->context->language->id,
            'id_lang' => (int) $this->context->language->id,
            'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/everpsblog/views/img/',
            'animated' => $animate,
            'show_featured_post' => true,
            'show_featured_cat' => (bool) Configuration::get('EVERBLOG_SHOW_FEAT_CAT'),
            'facet_url' => $facet_url,
            'sort_orders' => $sortOrders,
            'sort_selected' => $sortSelected ? $sortSelected['label'] : null,
        ]);
        $this->setTemplate($this->getFrontThemeTemplatePath('blog.tpl'));
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_BLOG_LAYOUT');
    }

    public function getCanonicalURL()
    {
        if (Tools::getValue('page')) {
            return;
        }
        return $this->context->link->getModuleLink(
            $this->module->name,
            'blog'
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
        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        if ((bool) Context::getContext()->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-' . Configuration::get('EVERPSBLOG_BLOG_LAYOUT')] = true;
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
