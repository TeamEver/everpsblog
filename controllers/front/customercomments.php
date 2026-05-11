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
use PrestaShop\Module\Everpsblog\Controller\Front\FrontBlogDataProviderTrait;

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class EverPsBlogcustomercommentsModuleFrontController extends AbstractFrontController
{
    use FrontBlogDataProviderTrait;

    public $controller_name = 'customercomments';
    
    public function init()
    {
        parent::init();
        if ((bool) $this->context->customer->isLogged() === false
            || (bool) Configuration::get('EVERBLOG_ALLOW_COMMENTS') === false
        ) {
            Tools::redirect('index.php');
        }
    }


    public function initContent()
    {
        if ((bool) $this->context->customer->isLogged() === false) {
            Tools::redirect('index.php');
        }
        parent::initContent();
        $page = $this->context->controller->getTemplateVarPage();
        $page['meta']['title'] = $this->transShop('Your comments');
        $page['meta']['description'] = $this->transShop('Find all your comments on our blog');
        $this->context->smarty->assign('page', $page);
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $comments = $this->getFrontCommentsByEmail(
            $this->context->customer->email,
            (int) $this->context->language->id
        );
        $cust_comments = [];
        foreach ($comments as $comment) {
            $post = $this->getFrontPost(
                (int) $comment->id_ever_post,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            if (empty($post->id)) {
                continue;
            }
            $post->featured_image = $this->getBlogImageService()->getBlogImageUrl(
                (int) $post->id,
                (int) $this->context->shop->id,
                'post'
            );
            $cust_comments[] = array(
                'post' => $post,
                'comment' => $comment
            );
        }
        $blogUrl = $this->context->link->getModuleLink(
            'everpsblog',
            'blog',
            [],
            true
        );

        $this->context->smarty->assign([
                'blogUrl' => $blogUrl,
                'comments' => $comments,
                'cust_comments' => $cust_comments,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => (int) $this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__.'/modules/everpsblog/views/img/',
                'animated' => $animate,
        ]);
        $this->setTemplate($this->getFrontThemeTemplatePath('customercomments.tpl'));
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        $breadcrumb['links'][] = [
            'title' => $this->transShop('My blog comments'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'customercomments'
            ),
        ];
        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog-customercomments'] = true;
        $page['body_classes']['page-everblog-customer-id-' . (int) $this->context->customer->id] = true;
        if ((bool) $this->context->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
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
