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

include_once dirname(__FILE__).'/../../classes/controller/FrontController.php';

class EverPsBlogfeedModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $feed;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $post_number;
    public $controller_name = 'feed';

    public function init()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->module_name = 'everpsblog';
        $this->errors = [];
        if ((bool)Configuration::get('EVERBLOG_RSS') === false) {
            Tools::redirect('index.php');
        }
        $this->allowed_feeds = array('category', 'tag', 'author', 'blog');
        if (!Tools::getValue('feed')
            || !in_array(Tools::getValue('feed'), $this->allowed_feeds)
        ) {
            Tools::redirect('index.php');
        }
        header('Content-type: text/xml');
        parent::init();
    }

    public function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsblog.feed'
            );
        }

        return parent::l($string, $specific, $class, $addslashes, $htmlentities);
    }

    public function initContent()
    {
        parent::initContent();
        switch (Tools::getValue('feed')) {
            case 'category':
                $feed_obj = new EverPsBlogCategory(
                    (int)Tools::getValue('id_obj'),
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $posts = EverPsBlogPost::getPostsByCategory(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    (int) $feed_obj->id,
                    0,
                    null,
                    'published',
                    true
                );
                break;

            case 'tag':
                $feed_obj = new EverPsBlogTag(
                    (int)Tools::getValue('id_obj'),
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $posts = EverPsBlogPost::getPostsByTag(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    (int) $feed_obj->id,
                    0,
                    null,
                    'published',
                    true
                );
                break;

            case 'author':
                $feed_obj = new EverPsBlogAuthor(
                    (int)Tools::getValue('id_obj'),
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $feed_obj->title = $feed_obj->nickhandle;
                $posts = EverPsBlogPost::getPostsByAuthor(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    (int) $feed_obj->id,
                    0,
                    null,
                    'published',
                    true
                );
                break;
            
            default:
                $feed_obj = new stdClass();
                // SEO title and meta desc
                $everblog_title = Configuration::getInt('EVERBLOG_TITLE');
                $meta_title = $everblog_title[(int) Context::getContext()->language->id];
                // Default blog text
                $everblog_top_text = Configuration::getInt('EVERBLOG_TOP_TEXT');
                $default_blog_top_text = $everblog_top_text[(int) Context::getContext()->language->id];
                $default_blog_top_text = EverPsBlogPost::changeShortcodes(
                    $default_blog_top_text,
                    (int) Context::getContext()->customer->id
                );
                $feed_obj->title = $meta_title;
                $feed_obj->content = $default_blog_top_text;
                $feed_obj->link_rewrite = $this->context->link->getModuleLink(
                    $this->module_name,
                    'blog',
                    [],
                    true,
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id
                );
                $posts_array = EverPsBlogPost::getPosts(
                    (int) $this->context->language->id,
                    (int) $this->context->shop->id,
                    0,
                    null,
                    'published',
                    true
                );
                $posts = [];
                foreach ($posts_array as $post_array) {
                    $post_obj = new stdClass();
                    $post_obj->id_ever_post = $post_array['id_ever_post'];
                    $post_obj->title = $post_array['title'];
                    $post_obj->content = $post_array['content'];
                    $post_obj->date_add = $post_array['date_add'];
                    $post_obj->link_rewrite = $post_array['link_rewrite'];
                    $posts[] = $post_obj;
                }
                break;
        }
        $feed_url = $this->context->link->getModuleLink(
            $this->module_name,
            'feed',
            array(
                'feed' => Tools::getValue('feed'),
                'id_obj' => Tools::getValue('id_obj')
            ),
            true,
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $this->context->smarty->assign(
            array(
                'feed_url' => $feed_url,
                'feed_obj' => $feed_obj,
                'posts' => $posts,
                'locale' => Context::getContext()->language->locale
            )
        );
        if ($this->isSeven) {
            $this->setTemplate('module:everpsblog/views/templates/front/feed.tpl');
        } else {
            $this->setTemplate('feed.tpl');
        }
    }
}
