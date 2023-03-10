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

class EverPsBlogcustomercommentsModuleFrontController extends EverPsBlogModuleFrontController
{
    public $controller_name = 'customercomments';
    
    public function init()
    {
        parent::init();
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        if ((bool)Context::getContext()->customer->isLogged() === false
            || (bool)Configuration::get('EVERBLOG_ALLOW_COMMENTS') === false
        ) {
            Tools::redirect('index.php');
        }
    }

    public function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsblog.customercomments'
            );
        }

        return parent::l($string, $specific, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
        if ((bool)Context::getContext()->customer->isLogged() === false) {
            Tools::redirect('index.php');
        }
        parent::initContent();
        if ($this->isSeven) {
            $page = $this->context->controller->getTemplateVarPage();
            $page['meta']['title'] = $this->l('Your comments');
            $page['meta']['description'] = $this->l('Find all your comments on our blog');
            $this->context->smarty->assign('page', $page);
        } else {
            $this->context->smarty->assign($this->l('Your comments'), true);
            $this->context->smarty->assign($this->l('Find all your comments on our blog'), true);
        }
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $comments = EverPsBlogComment::getCommentsByEmail(
            (string) $this->context->customer->email,
            (int) $this->context->language->id
        );
        $cust_comments = [];
        foreach ($comments as $comment) {
            $post = new EverPsBlogPost(
                (int) $comment->id_ever_post,
                (int) $this->context->shop->id,
                (int) $this->context->language->id
            );
            $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                (int) $post->id,
                (int) $this->context->shop->id,
                'post'
            );
            $cust_comments[] = array(
                'post' => $post,
                'comment' => $comment
            );
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            'everpsblog',
            'blog',
            array(),
            true
        );

        $this->context->smarty->assign(
            array(
                'blogUrl' => $blogUrl,
                'comments' => $comments,
                'cust_comments' => $cust_comments,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => (int) $this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__.'/modules/everpsblog/views/img/',
                'animated' => $animate,
            )
        );
        if ($this->isSeven) {
            $this->setTemplate('module:everpsblog/views/templates/front/customercomments.tpl');
        } else {
            $this->setTemplate('customercomments.tpl');
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        $breadcrumb['links'][] = array(
            'title' => $this->l('My blog comments'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'customercomments'
            ),
        );
        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog-customercomments'] = true;
        $page['body_classes']['page-everblog-customer-id-'.(int) $this->context->customer->id] = true;
        if ((bool)Context::getContext()->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        return $page;
    }
}
