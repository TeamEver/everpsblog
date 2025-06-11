<?php
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

require_once(dirname(__FILE__).'/../../classes/controller/FrontController.php');

class EverPsBlogsearchModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $query;
    public $post_number;
    public $controller_name = 'search';

    public function init()
    {
        $this->query = Tools::getValue('s');
        parent::init();
    }

    public function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Context::getContext()->getTranslator()->trans(
            $string,
            [],
            'Modules.Everpsblog.search'
        );
    }

    public function initContent()
    {
        parent::initContent();
        if (!$this->query) {
            Tools::redirect($this->context->link->getModuleLink('everpsblog', 'blog'));
        }
        $this->post_number = EverPsBlogPost::countPostsBySearch(
            $this->query,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $pagination = $this->getTemplateVarPagination($this->post_number);
        $posts = EverPsBlogPost::searchPost(
            $this->query,
            (int) $this->context->shop->id,
            (int) $this->context->language->id,
            (int) $pagination['items_shown_from'] - 1,
            (int) Configuration::get('EVERPSBLOG_PAGINATION')
        );
        $page = $this->context->controller->getTemplateVarPage();
        $page['meta']['title'] = $this->l('Search results for') . ' ' . $this->query;
        $page['meta']['description'] = $page['meta']['title'];
        $page['meta']['robots'] = 'noindex, follow';
        $this->context->smarty->assign('page', $page);
        $this->context->smarty->assign([
            'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
            'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
            'paginated' => Tools::getValue('page'),
            'post_number' => (int) $this->post_number,
            'pagination' => $pagination,
            'posts' => $posts,
            'query' => $this->query,
            'default_lang' => (int) $this->context->language->id,
            'id_lang' => $this->context->language->id,
            'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/everpsblog/views/img/',
            'animated' => Configuration::get('EVERBLOG_ANIMATE'),
            'show_featured_post' => (bool) Configuration::get('EVERBLOG_SHOW_FEAT_POST'),
        ]);
        $this->setTemplate('module:everpsblog/views/templates/front/search.tpl');
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_BLOG_LAYOUT');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->l('Blog'),
            'url' => $this->context->link->getModuleLink('everpsblog', 'blog'),
        ];
        $breadcrumb['links'][] = [
            'title' => $this->l('Search results'),
            'url' => $this->context->link->getModuleLink('everpsblog', 'search', ['s' => $this->query]),
        ];
        return $breadcrumb;
    }
}
