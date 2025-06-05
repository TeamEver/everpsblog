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

class EverPsBlogauthorModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $author;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $post_number;
    public $controller_name = 'author';

    public function init()
    {
        $this->module_name = 'everpsblog';
        $this->errors = [];
        $this->author = new EverPsBlogAuthor(
            (int) Tools::getValue('id_ever_author'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $customerGroups = Customer::getGroupsStatic(
            (int) $this->context->customer->id
        );
        if (isset($this->author->allowed_groups) && $this->author->allowed_groups) {
            if (is_array($this->author->allowed_groups)) {
                $allowedGroups = [];
            } else {
                $allowedGroups = json_decode($this->author->allowed_groups);
            }
            if (isset($customerGroups)
                && !empty($allowedGroups)
                && !array_intersect($allowedGroups, $customerGroups)
            ) {
                Tools::redirect('index.php?controller=404');
            }
        }
        parent::init();
        // if inactive post or unexists, redirect
        if (!(int) Tools::getValue('id_ever_author')
            || (bool) $this->author->active === false
        ) {
            Tools::redirect('index.php?controller=404');
        }
        $this->author->count = $this->author->count + 1;
        $this->author->save();
    }

    public function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        return $this->context->getTranslator()->trans(
            $string,
            [],
            'Modules.Everpsblog.author'
        );
    }

    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('id_ever_author')) {
            $this->post_number = EverPsBlogPost::countPostsByAuthor(
                (int) Tools::getValue('id_ever_author'),
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            // Pagination only if there is still some posts
            $pagination = $this->getTemplateVarPagination(
                $this->post_number
            );
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->author->indexable) {
                $seo_index = 'index';
            } else {
                $seo_index = 'noindex';
            }
            if ($this->author->follow) {
                $seo_follow = 'follow';
            } else {
                $seo_follow = 'nofollow';
            }
            $page = $this->context->controller->getTemplateVarPage();
            $page['meta']['robots'] = $seo_index . ', ' . $seo_follow;
            if (Tools::getValue('page')) {
                $meta_title = $this->l('Page : ') . Tools::getValue('page') . ' | ' . $this->author->meta_title;
                $meta_description = $this->l('Page : ') . Tools::getValue('page') . ' | ' . $this->author->meta_description;
            } else {
                $meta_title = $this->author->meta_title;
                $meta_description = $this->author->meta_description;
            }
            $page['meta']['title'] = $this->author->meta_title;
            $page['meta']['description'] = $this->author->meta_description;
            $this->context->smarty->assign('page', $page);
            // Now prepare template and show it
            // Prepare shortcodes
            $this->author->content = 
                $this->author->content;
            $this->author->bottom_content = 
                $this->author->bottom_content;
            $this->author->nickhandle = 
                $this->author->nickhandle;
            $posts = EverPsBlogPost::getPostsByAuthor(
                (int) $this->context->language->id,
                (int) $this->context->shop->id,
                (int) $this->author->id,
                (int) $pagination['items_shown_from'] - 1
            );
            Hook::exec('actionBeforeEverAuthorInitContent', [
                'blog_author' => $this->author,
            ]);
            $social_share_links = [];
            $social_share_links['facebook'] = [
                'label' => $this->trans('Share', [], 'Modules.Everpsblog.Shop'),
                'class' => 'facebook',
                'url' => 'https://www.facebook.com/sharer.php?u=' . $page['canonical'],
            ];
            $social_share_links['twitter'] = [
                'label' => $this->trans('Tweet', [], 'Modules.Everpsblog.Shop'),
                'class' => 'twitter',
                'url' => 'https://twitter.com/intent/tweet?text=' . $this->author->nickhandle . ' ' . $page['canonical'],
            ];
            $file_url = EverPsBlogImage::getBlogImageUrl(
                (int) $this->author->id,
                (int) $this->context->shop->id,
                'author'
            );
            $feed_url = $this->context->link->getModuleLink(
                $this->module_name,
                'feed',
                [
                    'feed' => 'author',
                    'id_obj' => $this->author->id,
                ],
                true,
                (int) $this->context->language->id,
                (int) $this->context->shop->id
            );
            $this->context->smarty->assign([
                'blogcolor' => Configuration::get('EVERBLOG_CSS_FILE'),
                'blog_type' => Configuration::get('EVERPSBLOG_TYPE'),
                'allow_feed' => (bool) Configuration::get('EVERBLOG_RSS'),
                'feed_url' => $feed_url,
                'featured_image' => $file_url,
                'posts' => $posts,
                'paginated' => Tools::getValue('page'),
                'post_number' => (int) $this->post_number,
                'pagination' => $pagination,
                'social_share_links' => $social_share_links,
                'author' => $this->author,
                'default_lang' => (int) $this->context->language->id,
                'id_lang' => (int) $this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/everpsblog/views/img/',
                'animated' => (bool) $animate,
                'logged' => (bool) $this->context->customer->isLogged(),
            ]);
            $this->setTemplate('module:everpsblog/views/templates/front/author.tpl');
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_AUTHOR_LAYOUT');
    }

    public function getBreadcrumbLinks()
    {
        $this->author = new EverPsBlogAuthor(
            (int) Tools::getValue('id_ever_author'),
            (int) $this->context->language->id,
            (int) $this->context->shop->id
        );
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->l('Blog'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'blog'
            ),
        ];
        $breadcrumb['links'][] = [
            'title' => 
                $this->author->nickhandle,
            'url' => $this->context->link->getModuleLink(
                $this->module->name,
                'author',
                [
                    'id_ever_author' => $this->author->id,
                    'link_rewrite' => $this->author->link_rewrite,
                ]
            ),
        ];
        return $breadcrumb;
    }

    public function getCanonicalURL()
    {
        if (Tools::getValue('page')) {
            return;
        }
        return $this->context->link->getModuleLink(
            'everpsblog',
            'author',
            [
                'id_ever_author' => $this->author->id,
                'link_rewrite' => $this->author->link_rewrite,
            ]
        );
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog'] = true;
        $page['body_classes']['page-everblog-author'] = true;
        $page['body_classes']['page-everblog-author-id-' . (int) $this->author->id] = true;
        if ((bool) $this->context->customer->isLogged()) {
            $page['body_classes']['page-everblog-logged-in'] = true;
        }
        $page['body_classes']['page-everblog-' . Configuration::get('EVERPSBLOG_AUTHOR_LAYOUT')] = true;
        return $page;
    }
}
