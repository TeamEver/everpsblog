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
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTaxonomy.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogImage.php';

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlogcategoryModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $author;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $nbr_blogs;

    public function init()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->category = new EverPsBlogCategory(
            (int)Tools::getValue('id_ever_category'),
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        parent::init();
        $this->parent_categories = EverPsBlogTaxonomy::getCategoryParentsTaxonomy(
            (int)$this->category->id
        );
        // if inactive category or unexists, redirect
        if (!$this->category->active
            || $this->category->is_root_category
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
        if (Tools::getValue('id_ever_category')) {
            $this->post_number = EverPsBlogPost::countPostsByCategory(
                (int)Tools::getValue('id_ever_category'),
                (int)$this->context->language->id,
                (int)$this->context->shop->id
            );
            // Pagination only if there is still some posts
            $pagination = $this->getTemplateVarPagination(
                $this->post_number
            );
            // die(var_dump($pagination['total_items']));
            // end pagination
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->category->index) {
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
            if (!Tools::getValue('page')) {
                $page['meta']['robots'] = $seo_index.', '.$seo_follow;
            } else {
                $page['meta']['robots'] = 'noindex, follow';
            }
            $page['meta']['title'] = $this->category->meta_title;
            $page['meta']['description'] = $this->category->meta_description;
            $this->context->smarty->assign('page', $page);
            $posts = EverPsBlogPost::getPostsByCategory(
                (int)$this->context->language->id,
                (int)$this->context->shop->id,
                (int)$this->category->id,
                (int)$pagination['items_shown_from'] - 1
            );
            Hook::exec('actionBeforeEverCategoryInitContent', array(
                'blog_category' => $this->category,
                'blog_posts' => $posts
            ));
            $file_url = EverPsBlogImage::getBlogImageUrl(
                (int)$this->category->id,
                (int)$this->context->shop->id,
                'category'
            );
            $this->context->smarty->assign(
                array(
                    'featured_image' => $file_url,
                    'paginated' => Tools::getValue('page'),
                    'post_number' => (int)$this->post_number,
                    'pagination' => $pagination,
                    'category' => $this->category,
                    'posts' => $posts,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                    'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/everpsblog/views/img/',
                    'animated' => $animate,
                )
            );
            if ($this->isSeven) {
                $this->setTemplate('module:everpsblog/views/templates/front/category.tpl');
            } else {
                $this->setTemplate('category.tpl');
            }
        } else {
            Tools::redirect('index.php');
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_CAT_LAYOUT');
    }

    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink(
            'everpsblog',
            'category',
            array(
                'id_ever_category' => $this->category->id,
                'link_rewrite' => $this->category->link_rewrite
            )
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
        foreach ($this->parent_categories as $parent_category) {
            $category = new EverPsBlogCategory(
                (int)$parent_category,
                (int)$this->context->language->id,
                (int)$this->context->shop->id
            );
            if ((bool)$category->is_root_category === false
                && (int)$category->id > 0
            ) {
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
        $breadcrumb['links'][] = array(
            'title' => $this->category->title,
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'category',
                array(
                    'id_ever_category' => (int)$this->category->id,
                    'link_rewrite' => $this->category->link_rewrite
                )
            ),
        );
        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog-category'] = true;
        return $page;
    }
}
