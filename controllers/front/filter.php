<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Everpsblog\Controller\Front\AbstractFrontController;
use PrestaShop\Module\Everpsblog\Controller\Front\FrontBlogDataProviderTrait;

class EverPsBlogfilterModuleFrontController extends AbstractFrontController
{
    use FrontBlogDataProviderTrait;

    public function displayAjax()
    {
        $idCategory = (int) Tools::getValue('category');
        $idTag = (int) Tools::getValue('tag');
        $page = (int) Tools::getValue('page', 1);
        $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
        $start = ($page - 1) * $limit;

        $posts = $this->getFilteredFrontPosts(
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            $idCategory ?: null,
            $idTag ?: null,
            $start,
            $limit
        );

        $this->context->smarty->assign([
            'posts' => $posts,
            'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
            'animated' => Configuration::get('EVERBLOG_ANIMATE'),
            'show_featured_post' => true,
            'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/everpsblog/views/img/',
        ]);

        $html = $this->context->smarty->fetch('module:everpsblog/views/templates/front/ajax_posts.tpl');
        $payload = json_encode(['html' => $html]);

        die(false !== $payload ? $payload : '{"html":""}');
    }
}
