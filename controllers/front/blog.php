<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @link http://www.team-ever.com
 * @copyright Teamm Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
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

class EverPsBlogblogModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $post_number;

    public function init()
    {
        parent::init();
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
    }

    protected function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans($string);
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
        parent::initContent();
        $this->post_number = EverPsBlogPost::countPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
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
        $this->context->smarty->assign(
            array(
                'post_number' => (int)$this->post_number,
                'pagination' => $pagination,
                'everpsblog' => $everpsblogposts,
                'evercategory' => $evercategories,
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/everpsblog/views/img/',
                'animated' => $animate,
            )
        );
        $this->setTemplate('module:everpsblog/views/templates/front/blog.tpl');
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
}
