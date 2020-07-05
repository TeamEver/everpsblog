<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
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

class EverPsBlogcustomercommentsModuleFrontController extends EverPsBlogModuleFrontController
{
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

        return parent::l($string, $specific, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
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
            (string)$this->context->customer->email,
            (int)$this->context->language->id
        );
        $cust_comments = array();
        foreach ($comments as $comment) {
            $post = new EverPsBlogPost(
                (int)$comment->id_ever_post,
                (int)$this->context->shop->id,
                (int)$this->context->language->id
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
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/everpsblog/views/img/',
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
        return $page;
    }
}
