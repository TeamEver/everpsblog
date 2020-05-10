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

class EverPsBlogtagModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $nbr_blogs;

    public function init()
    {
        $this->tag = new EverPsBlogTag(
            (int)Tools::getValue('id_ever_tag'),
            (int)$this->context->shop->id,
            (int)$this->context->language->id
        );
        parent::init();
        // if inactive tag or unexists, redirect
        if (!$this->tag->active) {
            Tools::redirect('index.php');
        }
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
        if (Tools::getValue('id_ever_tag')) {
            $this->post_number = EverPsBlogPost::countPostsByTag(
                (int)Tools::getValue('id_ever_tag'),
                (int)$this->context->language->id,
                (int)$this->context->shop->id
            );
            // Pagination only if there is still some posts
            $pagination = $this->getTemplateVarPagination($this->post_number);
            // end pagination
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->tag->index) {
                $seo_index = 'index';
            } else {
                $seo_index = 'noindex';
            }
            if ($this->tag->follow) {
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
            $page['meta']['title'] = $this->tag->meta_title;
            $page['meta']['description'] = $this->tag->meta_description;
            $this->context->smarty->assign('page', $page);
            $posts = EverPsBlogPost::getPostsByTag(
                (int)$this->context->language->id,
                (int)$this->context->shop->id,
                (int)$this->tag->id,
                (int)$pagination['items_shown_from'] - 1
            );

            $this->context->smarty->assign(
                array(
                    'paginated' => Tools::getValue('page'),
                    'post_number' => (int)$this->post_number,
                    'pagination' => $pagination,
                    'tag' => $this->tag,
                    'posts' => $posts,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                    'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/everpsblog/views/img/',
                    'animated' => $animate,
                )
            );
            if ($this->isSeven) {
                $this->setTemplate('module:everpsblog/views/templates/front/tag.tpl');
            } else {
                $this->setTemplate('tag.tpl');
            }
        } else {
            Tools::redirect('index.php');
        }
    }

    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink(
            'everpsblog',
            'tag',
            array(
                'id_ever_tag' => $this->tag->id,
                'link_rewrite' => $this->tag->link_rewrite
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
            'title' => $this->tag->title,
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'tag',
                array(
                    'id_ever_tag' => (int)$this->tag->id,
                    'link_rewrite' => $this->tag->link_rewrite
                )
            ),
        );
        return $breadcrumb;
    }

    public function getTemplateVarPage() {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog-tag'] = true;
        return $page;
    }
}
