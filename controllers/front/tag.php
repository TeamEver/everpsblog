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

include_once(dirname(__FILE__).'/../../classes/controller/FrontController.php');

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlogtagModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $author;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $nbr_blogs;
    public $controller_name = 'tags';

    public function init()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->module_name = 'everpsblog';
        $this->tag = new EverPsBlogTag(
            (int)Tools::getValue('id_ever_tag'),
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        parent::init();
        // if inactive tag or unexists, redirect
        if ((bool)$this->tag->active === false) {
            Tools::redirect('index.php');
        }
        $this->tag->count = $this->tag->count + 1;
        $this->tag->save();
    }

    public function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsblog.tag'
            );
        }

        return parent::l($string, $specific, $class, $addslashes, $htmlentities);
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
            Hook::exec('actionBeforeEverTagInitContent', array(
                'blog_tag' => $this->tag,
                'blog_posts' => $posts
            ));
            $file_url = EverPsBlogImage::getBlogImageUrl(
                (int)$this->tag->id,
                (int)$this->context->shop->id,
                'tag'
            );
            $feed_url = $this->context->link->getModuleLink(
                $this->module_name,
                'feed',
                array(
                    'feed' => 'tag',
                    'id_obj' => $this->tag->id
                ),
                true,
                (int)$this->context->language->id,
                (int)$this->context->shop->id
            );
            $this->context->smarty->assign(
                array(
                    'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                    'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
                    'allow_feed' => (bool)Configuration::get('EVERBLOG_RSS'),
                    'feed_url' => $feed_url,
                    'featured_image' => $file_url,
                    'paginated' => Tools::getValue('page'),
                    'post_number' => (int)$this->post_number,
                    'pagination' => $pagination,
                    'tag' => $this->tag,
                    'posts' => $posts,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                    'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/everpsblog/views/img/',
                    'animated' => $animate,
                    'show_featured_tag' => (bool)Configuration::get('EVERBLOG_SHOW_FEAT_TAG'),
                )
            );
            $this->setTemplate('module:everpsblog/views/templates/front/tag.tpl');
        } else {
            Tools::redirect('index.php');
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_TAG_LAYOUT');
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

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        $page['body_classes']['page-everblog-tag'] = true;
        $page['body_classes']['page-everblog-tag-id-'.(int)$this->tag->id] = true;
        if ((bool)Context::getContext()->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-'.Configuration::get('EVERPSBLOG_TAG_LAYOUT')] = true;
        return $page;
    }
}
