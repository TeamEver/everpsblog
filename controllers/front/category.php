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

class EverPsBlogcategoryModuleFrontController extends EverPsBlogModuleFrontController
{
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
            (int)$this->context->shop->id,
            (int)$this->context->language->id
        );
        parent::init();
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

        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('id_ever_category')) {
            $this->post_number = EverPsBlogPost::countPostsByCategory(
                (int)Tools::getValue('id_ever_category'),
                (int)$this->context->shop->id,
                (int)$this->context->language->id
            );
            // Pagination only if there is still some posts
            $pagination = $this->getTemplateVarPagination(
                $this->post_number
            );
            // die(var_dump($pagination['total_items']));
            if (Tools::getValue('page')
                && (int)$this->post_number > $pagination['total_items']
            ) {
                Tools::redirect('index.php');
            }
            if (Tools::getValue('page')) {
                if ((int)Tools::getValue('page') > 1) {
                    if ($pagination['items_shown_to'] >= $pagination['total_items']) {
                        $this->context->smarty->assign(
                            array(
                                'previous_page' => (int)Tools::getValue('page') - 1,
                            )
                        );
                    } else {
                        $this->context->smarty->assign(
                            array(
                                'previous_page' => (int)Tools::getValue('page') - 1,
                                'next_page' => (int)Tools::getValue('page') + 1,
                            )
                        );
                    }
                }
            } else {
                if ($this->post_number > $pagination['total_items']) {
                    $this->context->smarty->assign(
                        array(
                            'next_page' => 2,
                        )
                    );
                }
            }
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
            if ($this->isSeven) {
                $page = $this->context->controller->getTemplateVarPage();
                $page['meta']['robots'] = $seo_index.', '.$seo_follow;
                $page['meta']['title'] = $this->category->meta_title;
                $page['meta']['description'] = $this->category->meta_description;
                $this->context->smarty->assign('page', $page);
            } else {
                $this->context->smarty->assign($index, $seo_index);
                $this->context->smarty->assign($follow, $seo_follow);
                $this->context->smarty->assign($this->category->meta_title, true);
                $this->context->smarty->assign($this->category->meta_description, true);
            }
            $posts = EverPsBlogPost::getPostsByCategory(
                (int)$this->context->language->id,
                (int)$this->context->shop->id,
                (int)$this->category->id,
                (int)$pagination['items_shown_from'] - 1
            );

            $this->context->smarty->assign(
                array(
                    'post_number' => (int)$this->post_number,
                    'pagination' => $pagination,
                    'category' => $this->category,
                    'posts' => $posts,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                    'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/everpsblog/views/img/',
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
}
