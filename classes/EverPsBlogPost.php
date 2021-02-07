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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\AbstractLazyArray;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Core\Product\ProductInterface;

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCleaner.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogImage.php';

class EverPsBlogPost extends ObjectModel
{
    public $id_ever_post;
    public $meta_title;
    public $meta_description;
    public $link_rewrite;
    public $title;
    public $content;
    public $id_lang;
    public $id_shop;
    public $id_author;
    public $date_add;
    public $date_upd;
    public $post_status;
    public $post_categories;
    public $post_tags;
    public $post_products;
    public $index;
    public $follow;
    public $sitemap;
    public $active;
    public $limit;
    public $count;

    public static $definition = array(
        'table' => 'ever_blog_post',
        'primary' => 'id_ever_post',
        'multilang' => true,
        'fields' => array(
            'title' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'meta_title' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'meta_description' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'link_rewrite' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite'
            ),
            'content' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
            'id_author' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
            'index' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
            'follow' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
            'sitemap' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
            'post_status' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isName',
                'required' => true
            ),
            'post_categories' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'post_tags' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'post_products' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'count' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
        )
    );

    public static function getPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::getPosts_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$start
        .'_'
        .(int)$limit
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            if (!(int)$limit) {
                $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $current_context = Context::getContext();
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int)$limit, (int)$start);
            $posts = Db::getInstance()->executeS($sql);
            $return = array();
            if ($current_context->controller->controller_type == 'front'
                || $current_context->controller->controller_type == 'modulefront'
            ) {
                foreach ($posts as $post) {
                    $post['content'] = self::changeShortcodes(
                        $post['content'],
                        (int)Context::getContext()->customer->id
                    );
                    $post['title'] = self::changeShortcodes(
                        $post['title'],
                        (int)Context::getContext()->customer->id
                    );
                    // Length
                    $post['title'] = Tools::substr(
                        $post['title'],
                        0,
                        (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                    );
                    $post['content'] = Tools::substr(
                        $post['content'],
                        0,
                        (int)Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                    $post['featured_image'] = EverPsBlogImage::getBlogImageUrl(
                        (int)$post['id_ever_post'],
                        (int)$id_shop,
                        'post'
                    );
                    $return[] = $post;
                }
            } else {
                $return = $posts;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getLatestPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::getLatestPosts_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$start
        .'_'
        .(int)$limit
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            if (!(int)$limit) {
                $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $sql->limit((int)$limit, (int)$start);
            $sql->orderBy('bp.date_add DESC');
            $sql->orderBy('bp.id_ever_post DESC');
            $posts = Db::getInstance()->executeS($sql);
            $return = array();
            foreach ($posts as $post_array) {
                    $post = new self(
                        (int)$post_array['id_ever_post'],
                        (int)$id_lang,
                        (int)$id_shop
                    );
                    $post->title = self::changeShortcodes(
                        $post->title,
                        (int)Context::getContext()->customer->id
                    );
                    $post->content = self::changeShortcodes(
                        $post->content,
                        (int)Context::getContext()->customer->id
                    );
                    // Length
                    $post->title = Tools::substr(
                        $post->title,
                        0,
                        (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                    );
                    $post->content = Tools::substr(
                        strip_tags($post->content),
                        0,
                        (int)Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                    $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                        (int)$post->id,
                        (int)$id_shop,
                        'post'
                    );
                    $return[] = $post;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostsByTag(
        $id_lang,
        $id_shop,
        $id_tag,
        $start = 0,
        $limit = null,
        $post_status = 'published'
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByTag_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$id_tag
        .'_'
        .(int)$start
        .'_'
        .(int)$limit
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            if (!(int)$limit) {
                $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->leftJoin(
                'ever_blog_post_tag',
                'bpt',
                'bpt.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $sql->where('bpt.id_ever_post_tag = '.(int)$id_tag);
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int)$limit, (int)$start);
            $posts = Db::getInstance()->executeS($sql);
            $return = array();
            foreach ($posts as $post) {
                $post = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int)Context::getContext()->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    $post->content,
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int)$post->id,
                    (int)$id_shop,
                    'post'
                );
                $return[] = $post;
            }
            if ($return) {
                Cache::store($cache_id, $return);
                return $return;
            }
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostsByCategory(
        $id_lang,
        $id_shop,
        $id_category,
        $start = 0,
        $limit = null,
        $post_status = 'published'
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByTag_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$id_category
        .'_'
        .(int)$start
        .'_'
        .(int)$limit
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            if (!(int)$limit) {
                $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->from('ever_blog_post', 'ebp');
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->leftJoin(
                'ever_blog_post_category',
                'bpc',
                'bpc.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $sql->where('bpc.id_ever_post_category = '.(int)$id_category);
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int)$limit, (int)$start);
            $posts = Db::getInstance()->executeS($sql);
            $return = array();
            foreach ($posts as $post_array) {
                $post = new self(
                    (int)$post_array['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int)Context::getContext()->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    $post->content,
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int)$post->id,
                    (int)$id_shop,
                    'post'
                );
                $return[] = $post;
            }
            if ($return) {
                Cache::store($cache_id, $return);
                return $return;
            }
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostsByAuthor(
        $id_lang,
        $id_shop,
        $id_author,
        $start = 0,
        $limit = null,
        $post_status = 'published'
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByAuthor_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$id_author
        .'_'
        .(int)$start
        .'_'
        .(int)$limit
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            if (!(int)$limit) {
                $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_author = '.(int)$id_author);
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int)$limit, (int)$start);
            $posts = Db::getInstance()->executeS($sql);
            $return = array();
            foreach ($posts as $post) {
                $post = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int)Context::getContext()->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    $post->content,
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int)$post->id,
                    (int)$id_shop,
                    'post'
                );
                $return[] = $post;
            }
            if ($return) {
                Cache::store($cache_id, $return);
                return $return;
            }
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostsByProduct(
        $id_lang,
        $id_shop,
        $id_product,
        $start = 0,
        $limit = null,
        $post_status = 'published'
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByProduct_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$id_product
        .'_'
        .(int)$start
        .'_'
        .(int)$limit
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            if (!(int)$limit) {
                $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->leftJoin(
                'ever_blog_post_product',
                'bpp',
                'bpp.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $sql->where('bpp.id_ever_post_product = '.(int)$id_product);
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int)$limit, (int)$start);
            $posts = Db::getInstance()->executeS($sql);
            $return = array();
            foreach ($posts as $post_array) {
                $post = new self(
                    (int)$post_array['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int)Context::getContext()->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    $post->content,
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int)$post->id,
                    (int)$id_shop,
                    'post'
                );
                $return[] = $post;
            }
            if ($return) {
                Cache::store($cache_id, $return);
                return $return;
            }
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostCategories($id_ever_post, $id_shop, $id_lang, $active = true)
    {
        $cache_id = 'EverPsBlogPost::getPostCategories_'
        .(int)$id_ever_post
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$id_lang
        .'_'
        .(int)$active;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('post_categories');
            $sql->from('ever_blog_post', 'ep');
            $sql->leftJoin(
                'ever_blog_post_lang',
                'bpl',
                'ep.id_ever_post = bpl.id_ever_post'
            );
            $sql->where(
                'ep.id_ever_post = '.(int)$id_ever_post
            );
            $sql->where(
                'ep.id_shop = '.(int)$id_shop
            );
            $sql->where(
                'ep.id_lang = '.(int)$id_lang
            );
            $sql->where(
                'ep.active = '.(int)$active
            );
            $sql->orderBy('ep.date_add DESC');
            // $sql->limit((int)$limit);
            $post_categories = Db::getInstance()->getValue($sql);
            $return = json_decode($post_categories);
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostByLinkRewrite(
        $link_rewrite
    ) {
        $cache_id = 'EverPsBlogPost::getPostByLinkRewrite_'
        .(string)$link_rewrite;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_post');
            $sql->from('ever_blog_post_lang');
            $sql->where('link_rewrite = "'.pSQL($link_rewrite).'"');
            $id_ever_post = Db::getInstance()->getValue($sql);
            $return = new self(
                (int)$id_ever_post
            );
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function countPosts($id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPosts_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('COUNT(*)');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $count = Db::getInstance()->getValue($sql);
            if ($count) {
                Cache::store($cache_id, $count);
                return (int)$count;
            }
        }
        return Cache::retrieve($cache_id);
    }

    public static function countPostsByTag($id_tag, $id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPostsByTag_'
        .(int)$id_tag
        .'_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->leftJoin(
                'ever_blog_post_tag',
                'bpc',
                'bpc.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bpc.id_ever_post_tag = '.(int)$id_tag);
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $posts = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, count($posts));
            return count($posts);
        }
        return Cache::retrieve($cache_id);
    }

    public static function countPostsByCategory($id_category, $id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPostsByCategory_'
        .(int)$id_category
        .'_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->leftJoin(
                'ever_blog_post_category',
                'bpc',
                'bpc.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bpc.id_ever_post_category = '.(int)$id_category);
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $posts = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, count($posts));
            return count($posts);
        }
        return Cache::retrieve($cache_id);
    }

    public static function countPostsByAuthor($id_author, $id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPostsByAuthor_'
        .(int)$id_author
        .'_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .$post_status;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_post_lang', 'bpl');
            $sql->leftJoin(
                'ever_blog_post',
                'bp',
                'bp.id_ever_post = bpl.id_ever_post'
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_author = '.(int)$id_author);
            $sql->where('bp.id_shop = '.(int)$id_shop);
            $sql->where('bpl.id_lang = '.(int)$id_lang);
            $posts = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, count($posts));
            return count($posts);
        }
        return Cache::retrieve($cache_id);
    }

    public static function changeShortcodes($message, $id_entity = false)
    {
        $link = new Link();
        $contactLink = $link->getPageLink('contact');
        if ($id_entity && $id_entity > 0) {
            $entity = new Customer(
                (int)$id_entity
            );
            $gender = new Gender(
                (int)$entity->id_gender,
                (int)$entity->id_lang
            );
            $entityShortcodes = array(
                '[entity_lastname]' => $entity->lastname,
                '[entity_firstname]' => $entity->firstname,
                '[entity_company]' => $entity->company,
                '[entity_siret]' => $entity->siret,
                '[entity_ape]' => $entity->ape,
                '[entity_birthday]' => $entity->birthday,
                '[entity_website]' => $entity->website,
                '[entity_gender]' => $gender->name,
            );
        } else {
            $entityShortcodes = array(
                '[entity_lastname]' => '',
                '[entity_firstname]' => '',
                '[entity_company]' => '',
                '[entity_siret]' => '',
                '[entity_ape]' => '',
                '[entity_birthday]' => '',
                '[entity_website]' => '',
                '[entity_gender]' => '',
            );
        }
        $defaultShortcodes = array(
            '[shop_url]' => Tools::getShopDomainSsl(true),
            '[shop_name]'=> (string)Configuration::get('PS_SHOP_NAME'),
            '[start_cart_link]' => '<a href="'
            .Tools::getShopDomainSsl(true)
            .'/index.php?controller=cart&action=show" rel="nofollow" target="_blank">',
            '[end_cart_link]' => '</a>',
            '[start_shop_link]' => '<a href="'
            .Tools::getShopDomainSsl(true)
            .'" target="_blank">',
            '[start_contact_link]' => '<a href="'.$contactLink.'" rel="nofollow" target="_blank">',
            '[end_shop_link]' => '</a>',
            '[end_contact_link]' => '</a>',
            '[1F600]' => '😀',
            '[1F601]' => '😁',
            '[1F602]' => '😂',
            '[1F603]' => '😃',
            '[1F604]' => '😄',
            '[1F605]' => '😅',
            '[1F606]' => '😆',
            '[1F607]' => '😇',
            '[1F608]' => '😈',
            '[1F609]' => '😉',
            '[1F60A]' => '😊',
            '[1F60B]' => '😋',
            '[1F60C]' => '😌',
            '[1F60D]' => '😍',
            '[1F60E]' => '😎',
            '[1F60F]' => '😏',
            '[1F610]' => '😐',
            '[1F611]' => '😑',
            '[1F612]' => '😒',
            '[1F613]' => '😓',
            '[1F614]' => '😔',
            '[1F615]' => '😕',
            '[1F616]' => '😖',
            '[1F617]' => '😗',
            '[1F618]' => '😘',
            '[1F619]' => '😙',
            '[1F61A]' => '😚',
            '[1F61B]' => '😛',
            '[1F61C]' => '😜',
            '[1F61D]' => '😝',
            '[1F61E]' => '😞',
            '[1F61F]' => '😟',
            '[1F620]' => '😠',
            '[1F621]' => '😡',
            '[1F622]' => '😢',
            '[1F623]' => '😣',
            '[1F624]' => '😤',
            '[1F625]' => '😥',
            '[1F626]' => '😦',
            '[1F627]' => '😧',
            '[1F628]' => '😨',
            '[1F629]' => '😩',
            '[1F62A]' => '😪',
            '[1F62B]' => '😫',
            '[1F62C]' => '😬',
            '[1F62D]' => '😭',
            '[1F62E]' => '😮',
            '[1F62F]' => '😯',
            '[1F630]' => '😰',
            '[1F631]' => '😱',
            '[1F632]' => '😲',
            '[1F633]' => '😳',
            '[1F634]' => '😴',
            '[1F635]' => '😵',
            '[1F636]' => '😶',
            '[1F637]' => '😷',
            '[1F641]' => '🙁',
            '[1F642]' => '🙂',
            '[1F643]' => '🙃',
            '[1F644]' => '🙄',
            '[1F910]' => '🤐',
            '[1F911]' => '🤑',
            '[1F912]' => '🤒',
            '[1F913]' => '🤓',
            '[1F914]' => '🤔',
            '[1F915]' => '🤕',
            '[1F920]' => '🤠',
            '[1F921]' => '🤡',
            '[1F922]' => '🤢',
            '[1F923]' => '🤣',
            '[1F924]' => '🤤',
            '[1F925]' => '🤥',
            '[1F927]' => '🤧',
            '[1F928]' => '🤨',
            '[1F929]' => '🤩',
            '[1F92A]' => '🤪',
            '[1F92B]' => '🤫',
            '[1F92C]' => '🤬',
            '[1F92D]' => '🤭',
            '[1F92E]' => '🤮',
            '[1F92F]' => '🤯',
            '[1F9D0]' => '🧐',
        );
        $shortcodes = array_merge($entityShortcodes, $defaultShortcodes);
        foreach ($shortcodes as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }

    public static function dropBlogAuthorPosts($id_ever_author)
    {
        $sql = 'UPDATE '._DB_PREFIX_.'ever_blog_post
            SET id_author = 0
            WHERE id_author = '.(int)$id_ever_author.';
        ';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        } else {
            return true;
        }
    }
}
