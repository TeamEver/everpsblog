<?php
/**
 * 2019-2020 Team Ever
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

use PrestaShop\PrestaShop\Core\Product\Search\Pagination;

require_once(dirname(__FILE__).'/../../everpsblog.php');
require_once(dirname(__FILE__).'/../EverPsBlogPost.php');
require_once(dirname(__FILE__).'/../EverPsBlogCategory.php');
require_once(dirname(__FILE__).'/../EverPsBlogTag.php');

class EverPsBlogModuleFrontController extends ModuleFrontController
{
    protected $page = 1;
    protected $totalPerPage = 10;

    public function getTemplateVarPage()
    {
        $page_name = $this->getPageName();
        $id_lang = (int)$this->context->language->id;
        $seo = array(
            'title' => '',
            'description' => '',
            'keywords' => '',
            'robots' => '',
        );

        if ($page_name == 'module-everpsblog-category'
            && ($id_ever_category = Tools::getValue('id_ever_category'))
        ) {
            $sql = 'SELECT `title`,`meta_title`, `meta_description`, `index`, `follow`
                FROM `'._DB_PREFIX_.'ever_blog_category_lang` ebcl
                LEFT JOIN `'._DB_PREFIX_.'ever_blog_category` ebc
                ON ebcl.`id_ever_category` = ebc.`id_ever_category`
                WHERE ebcl.`id_lang` = '.(int)$id_lang.'
                AND ebcl.`id_ever_category` = '.(int)$id_ever_category;
        } elseif ($page_name == 'module-everpsblog-post'
            && ($id_ever_post = Tools::getValue('id_ever_post'))
        ) {
            $sql = 'SELECT `title`,`meta_title`, `meta_description`, `index`, `follow`
                FROM `'._DB_PREFIX_.'ever_blog_post_lang` ebpl
                LEFT JOIN `'._DB_PREFIX_.'ever_blog_post` ebp
                ON ebpl.`id_ever_post` = ebp.`id_ever_post`
                WHERE ebpl.`id_lang` = '.(int)$id_lang.'
                AND ebpl.`id_ever_post` = '.(int)$id_ever_post;
        } elseif ($page_name == 'module-everpsblog-tag'
            && ($id_ever_tag = Tools::getValue('id_ever_tag'))
        ) {
            $sql = 'SELECT `title`,`meta_title`, `meta_description`, `index`, `follow`
                FROM `'._DB_PREFIX_.'ever_blog_tag_lang` ebtl
                LEFT JOIN `'._DB_PREFIX_.'ever_blog_tag` ebt
                ON ebtl.`id_ever_tag` = ebt.`id_ever_tag`
                WHERE ebtl.`id_lang` = '.(int)$id_lang.'
                AND ebtl.`id_ever_tag` = '.(int)$id_ever_tag;
        } elseif ($page_name == 'module-everpsblog-author'
            && ($id_ever_author = Tools::getValue('id_ever_author'))
        ) {
            $sql = 'SELECT `nickhandle`,`meta_title`, `meta_description`, `index`, `follow`
                FROM `'._DB_PREFIX_.'ever_blog_author_lang` ebtl
                LEFT JOIN `'._DB_PREFIX_.'ever_blog_author` ebt
                ON ebtl.`id_ever_author` = ebt.`id_ever_author`
                WHERE ebtl.`id_lang` = '.(int)$id_lang.'
                AND ebtl.`id_ever_author` = '.(int)$id_ever_author;
        }

        // Set SEO metas per object
        if (isset($sql)) {
            $seo_metas = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
            if ((int)$seo_metas['index']) {
                $index = 'index';
            } else {
                $index = 'noindex';
            }
            if ((int)$seo_metas['follow']) {
                $follow = 'follow';
            } else {
                $follow = 'nofollow';
            }

            $seo['title'] = ($seo_metas['meta_title'] ? $seo_metas['meta_title'] : $seo_metas['title']);
            $seo['description'] = $seo_metas['meta_description'];
            $seo['keywords'] = '';
            $seo['robots'] = $index.', '.$follow;
        } else {
            $seo['title'] == $seo['title'];
            $seo['description'] == $seo['description'];
        }
        $page = parent::getTemplateVarPage();
        $page['meta'] = $seo;
        return $page;
    }

    public function init()
    {
        parent::init();
        $param = array();
        $page_name = Dispatcher::getInstance()->getController();
        Hook::exec('beforeEverBlogInit', array(
            'blog_page_name' => $page_name
        ));

        switch ($page_name) {
            case 'post':
                if (!$this->post) {
                    $this->post = new EverPsBlogPost(
                        (int)Tools::getValue('id_ever_post'),
                        (int)$this->context->language->id,
                        (int)$this->context->shop->id
                    );
                }
                $param = array(
                    'id_ever_post' => $this->post->id,
                    'link_rewrite' => $this->post->link_rewrite
                );
                break;

            case 'category':
                if (!$this->category) {
                    $this->category = new EverPsBlogCategory(
                        (int)Tools::getValue('id_ever_category'),
                        (int)$this->context->language->id,
                        (int)$this->context->shop->id
                    );
                }
                $param = array(
                    'id_ever_category' => $this->category->id,
                    'link_rewrite' => $this->category->link_rewrite
                );
                break;

            case 'tag':
                if (!$this->tag) {
                    $this->tag = new EverPsBlogTag(
                        (int)Tools::getValue('id_ever_tag'),
                        (int)$this->context->language->id,
                        (int)$this->context->shop->id
                    );
                }
                $param = array(
                    'id_ever_tag' => $this->tag->id,
                    'link_rewrite' => $this->tag->link_rewrite
                );
                break;

            case 'author':
                if (!$this->author) {
                    $this->author = new EverPsBlogAuthor(
                        (int)Tools::getValue('id_ever_author'),
                        (int)$this->context->language->id,
                        (int)$this->context->shop->id
                    );
                }
                $param = array(
                    'id_ever_author' => $this->author->id,
                    'link_rewrite' => $this->author->link_rewrite
                );
                break;
        }

        if ($param) {
            $canonical_url = $this->context->link->getModuleLink(
                'everpsblog',
                $page_name,
                $param
            );
            Hook::exec('afterEverBlogInit', array(
                'blog_page_name' => $page_name,
                'param' => $param
            ));
            $this->canonicalRedirection($canonical_url);
        }
    }

    protected function getTemplateVarPagination($total = 0)
    {
        $totalItems = (int)$total;
        $page = (int)Tools::getValue('page');
        $page = (int)Tools::getValue('page') ? (int)Tools::getValue('page') : 1;
        $totalPerPage = $this->totalPerPage ? (int)$this->totalPerPage : 10;
        $pagination = new Pagination();
        $pagination
            ->setPage($page)
            ->setPagesCount(
                (int)ceil((int)$totalItems / $totalPerPage)
            )
        ;
        $pages = array_map(function ($link) {
            $link['url'] = $this->updateQueryString(array(
                'page' => $link['page'] > 1 ? $link['page'] : null,
            ));

            return $link;
        }, $pagination->buildLinks());

        //Filter next/previous link on first/last page
        $pages = array_filter($pages, function ($page) use ($pagination) {
            if ('previous' === $page['type'] && 1 === $pagination->getPage()) {
                return false;
            }
            if ('next' === $page['type'] && $pagination->getPagesCount() === $pagination->getPage()) {
                return false;
            }

            return true;
        });

        $itemsShownFrom = ($totalPerPage * ($page - 1)) + 1;
        $itemsShownTo = $totalPerPage * $page;

        return array(
            'total_items' => $totalItems,
            'items_shown_from' => $itemsShownFrom,
            'items_shown_to' => ($itemsShownTo <= $totalItems) ? $itemsShownTo : $totalItems,
            'current_page' => $pagination->getPage(),
            'pages_count' => $pagination->getPagesCount(),
            'pages' => $pages,
            // Compare to 3 because there are the next and previous links
            'should_be_displayed' => (count($pagination->buildLinks()) > 3),
        );
    }

    protected function canonicalRedirection($canonical_url = '')
    {
        if (!$canonical_url
            || !Configuration::get('PS_CANONICAL_REDIRECT')
            || Tools::strtoupper($_SERVER['REQUEST_METHOD']) != 'GET'
        ) {
            return;
        }

        $match_url = (Configuration::get('PS_SSL_ENABLED')
            && ($this->ssl
                || Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) ? 'https://' : 'http://')
        .$_SERVER['HTTP_HOST']
        .$_SERVER['REQUEST_URI'];
        $match_url = rawurldecode($match_url);
        if (!preg_match(
            '/^'.Tools::pRegexp(rawurldecode($canonical_url), '/').'([&?].*)?$/',
            $match_url
        )) {
            $params = array();
            $str_params = '';
            $url_details = parse_url($canonical_url);

            if (!empty($url_details['query'])) {
                parse_str($url_details['query'], $query);
                foreach ($query as $key => $value) {
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);
                }
            }
            $excluded_key = array(
                'isolang',
                'id_lang',
                'controller',
                'id_ever_category',
                'id_ever_post',
                'id_ever_tag',
                'id_ever_author',
                'fc',
                'module'
            );
            foreach ($_GET as $key => $value) {
                if (!in_array($key, $excluded_key)
                    && Validate::isUrl($key)
                    && Validate::isUrl($value)
                ) {
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);
                }
            }

            $str_params = http_build_query($params, '', '&');
            if (!empty($str_params)) {
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url).'?'.$str_params;
            } else {
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url);
            }

            Context::getContext()->cookie->disallowWriting();

            if (defined('_PS_MODE_DEV_')
                && _PS_MODE_DEV_
                && $_SERVER['REQUEST_URI'] != __PS_BASE_URI__
            ) {
                die(
                    '[Debug] This page has moved<br />Please use the following URL instead: <a href="'
                    .$final_url
                    .'">'
                    .$final_url
                    .'</a>'
                );
            }

            header('HTTP/1.0 301 Moved');
            header('Cache-Control: no-cache');
            Tools::redirectLink($final_url);
        }
    }
}
