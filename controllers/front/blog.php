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

include_once(dirname(__FILE__).'/../../classes/controller/FrontController.php');
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogImage.php';

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlogblogModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $author;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $post_number;

    public function init()
    {
        parent::init();
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->module_name = 'everpsblog';
    }

    public function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsblog.blog'
            );
        }

        return parent::l($string, $specific, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
        parent::initContent();
        $this->post_number = EverPsBlogPost::countPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $this->featured_category = new Category(
            (int)Configuration::get('EVERBLOG_CAT_FEATURED'),
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $featured_products = $this->featured_category->getProducts(
            (int)$this->context->language->id,
            1,
            (int)Configuration::get('EVERPSBLOG_PAGINATION')
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

            $productsForTemplate = array();

            $presentationSettings->showPrices = $showPrice;

            if (is_array($featured_products)) {
                foreach ($featured_products as $productId) {
                    $productsForTemplate[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct(array('id_product' => $productId['id_product'])),
                        $this->context->language
                    );
                }
            }
            $this->context->smarty->assign(array(
                'everhome_products' => $productsForTemplate,
            ));
        }
        // pagination
        $pagination = $this->getTemplateVarPagination($this->post_number);
        // SEO title and meta desc
        $everblog_title = Configuration::getInt('EVERBLOG_TITLE');
        $meta_title = $everblog_title[(int)Context::getContext()->language->id];
        $everblog_desc = Configuration::getInt('EVERBLOG_META_DESC');
        $meta_desc = $everblog_desc[(int)Context::getContext()->language->id];
        $page = $this->context->controller->getTemplateVarPage();
        $page['meta']['title'] = $meta_title;
        $page['meta']['description'] = $meta_desc;
        if (!Tools::getValue('page')) {
            $page['meta']['robots'] = 'index, follow';
        } else {
            $page['meta']['robots'] = 'noindex, follow';
        }
        $this->context->smarty->assign('page', $page);
        $everpsblogposts = EverPsBlogPost::getPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            (int)$pagination['items_shown_from'] - 1
        );
        $evercategories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        // Default blog text
        $everblog_top_text = Configuration::getInt('EVERBLOG_TOP_TEXT');
        $default_blog_top_text = $everblog_top_text[(int)Context::getContext()->language->id];
        $default_blog_top_text = EverPsBlogPost::changeShortcodes(
            $default_blog_top_text,
            (int)Context::getContext()->customer->id
        );
        $everblog_bottom_text = Configuration::getInt('EVERBLOG_BOTTOM_TEXT');
        $default_blog_bottom_text = $everblog_bottom_text[(int)Context::getContext()->language->id];
        $default_blog_bottom_text = EverPsBlogPost::changeShortcodes(
            $default_blog_bottom_text,
            (int)Context::getContext()->customer->id
        );
        Hook::exec('actionBeforeEverBlogInitContent', array(
            'blog_post_number' => $this->post_number,
            'everpsblog' => $everpsblogposts,
            'everpsblogcategories' => $evercategories,
            'blog_page' => Tools::getValue('page')
        ));
        $feed_url = $this->context->link->getModuleLink(
            $this->module_name,
            'feed',
            array(
                'feed' => 'blog'
            ),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $this->context->smarty->assign(
            array(
                'allow_feed' => (bool)Configuration::get('EVERBLOG_RSS'),
                'feed_url' => $feed_url,
                'default_blog_top_text' => $default_blog_top_text,
                'default_blog_bottom_text' => $default_blog_bottom_text,
                'paginated' => Tools::getValue('page'),
                'post_number' => (int)$this->post_number,
                'pagination' => $pagination,
                'everpsblog' => $everpsblogposts,
                'evercategory' => $evercategories,
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/everpsblog/views/img/',
                'animated' => $animate,
                'pagination' => $pagination,
                'show_featured_cat' => (bool)Configuration::get('EVERBLOG_SHOW_FEAT_CAT'),
            )
        );
        $this->setTemplate('module:everpsblog/views/templates/front/blog.tpl');
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_BLOG_LAYOUT');
    }

    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink(
            'everpsblog',
            'blog'
        );
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->l('Blog'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'blog'
            ),
        );
        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        if ((bool)Context::getContext()->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-'.Configuration::get('EVERPSBLOG_BLOG_LAYOUT')] = true;
        return $page;
    }
}
