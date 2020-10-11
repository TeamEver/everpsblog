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
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogAuthor.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogAuthor.php';

class EverPsBlogauthorModuleFrontController extends EverPsBlogModuleFrontController
{
    protected $author;
    protected $category;
    protected $tag;
    protected $post;
    protected $blog;
    public $post_number;

    public function init()
    {
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->errors = array();
        $this->author = new EverPsBlogAuthor(
            (int)Tools::getValue('id_ever_author'),
            (int)$this->context->shop->id,
            (int)$this->context->language->id
        );
        parent::init();
        // if inactive post or unexists, redirect
        if (!(int)Tools::getValue('id_ever_author')
            || (bool)$this->author->active === false
        ) {
            Tools::redirect('index.php?controller=404');
        }
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
        if (Tools::getValue('id_ever_author')) {
            $this->post_number = EverPsBlogPost::countPostsByAuthor(
                (int)Tools::getValue('id_ever_author'),
                (int)$this->context->language->id,
                (int)$this->context->shop->id
            );
            // Pagination only if there is still some posts
            $pagination = $this->getTemplateVarPagination(
                $this->post_number
            );
            $animate = Configuration::get(
                'EVERBLOG_ANIMATE'
            );
            if ($this->author->index) {
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
            $page['meta']['title'] = $this->author->meta_title;
            $page['meta']['description'] = $this->author->meta_description;
            $this->context->smarty->assign('page', $page);
            // Now prepare template and show it
            // Prepare shortcodes
            $this->author->content = EverPsBlogPost::changeShortcodes(
                (string)$this->author->content,
                (int)Context::getContext()->customer->id
            );
            $this->author->nickhandle = EverPsBlogPost::changeShortcodes(
                (string)$this->author->nickhandle,
                (int)Context::getContext()->customer->id
            );
            $posts = EverPsBlogPost::getPostsByAuthor(
                (int)$this->context->language->id,
                (int)$this->context->shop->id,
                (int)$this->author->id,
                (int)$pagination['items_shown_from'] - 1
            );
            Hook::exec('actionBeforeEverAuthorInitContent', array(
                'blog_author' => $this->author
            ));
            $social_share_links = [];
            $social_share_links['facebook'] = [
                'label' => $this->trans('Share', [], 'Modules.Everpsblog.Shop'),
                'class' => 'facebook',
                'url' => 'https://www.facebook.com/sharer.php?u='.$page['canonical'],
            ];
            $social_share_links['twitter'] = [
                'label' => $this->trans('Tweet', [], 'Modules.Everpsblog.Shop'),
                'class' => 'twitter',
                'url' => 'https://twitter.com/intent/tweet?text='.$this->author->nickhandle.' '.$page['canonical'],
            ];
            $this->context->smarty->assign(
                array(
                    'posts' => $posts,
                    'paginated' => Tools::getValue('page'),
                    'post_number' => (int)$this->post_number,
                    'pagination' => $pagination,
                    'social_share_links' => $social_share_links,
                    'author' => $this->author,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => (int)$this->context->language->id,
                    'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/everpsblog/views/img/',
                    'animated' => (bool)$animate,
                    'logged' => (bool)$this->context->customer->isLogged(),
                )
            );
            if ($this->isSeven) {
                $this->setTemplate('module:everpsblog/views/templates/front/author.tpl');
            } else {
                $this->setTemplate('author.tpl');
            }
        }
    }

    public function getLayout()
    {
        return Configuration::get('EVERPSBLOG_AUTHOR_LAYOUT');
    }

    public function getBreadcrumbLinks()
    {
        $this->author = new EverPsBlogAuthor(
            (int)Tools::getValue('id_ever_author'),
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->l('Blog'),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'blog'
            ),
        );
        $breadcrumb['links'][] = array(
            'title' => EverPsBlogPost::changeShortcodes(
                $this->author->nickhandle,
                Context::getContext()->customer->id
            ),
            'url' => $this->context->link->getModuleLink(
                'everpsblog',
                'author',
                array(
                    'id_ever_author' => $this->author->id,
                    'link_rewrite' => $this->author->link_rewrite
                )
            ),
        );
        return $breadcrumb;
    }

    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink(
            'everpsblog',
            'author',
            array(
                'id_ever_author' => $this->author->id,
                'link_rewrite' => $this->author->link_rewrite
            )
        );
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-everblog-author'] = true;
        return $page;
    }
}
