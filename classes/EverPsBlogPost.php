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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\AbstractLazyArray;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Core\Product\ProductInterface;

require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogCleaner.php';
require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogImage.php';

class EverPsBlogPost extends ObjectModel
{
    public $id_ever_post;
    public $meta_title;
    public $meta_description;
    public $link_rewrite;
    public $title;
    public $content;
    public $excerpt;
    public $id_lang;
    public $id_shop;
    public $id_author;
    public $date_add;
    public $date_upd;
    public $post_status;
    public $id_default_category;
    public $allowed_groups;
    public $post_categories;
    public $post_tags;
    public $post_products;
    public $indexable;
    public $follow;
    public $sitemap;
    public $active;
    public $limit;
    public $psswd;
    public $starred;
    public $count;

    public static $definition = [
        'table' => 'ever_blog_post',
        'primary' => 'id_ever_post',
        'multilang' => true,
        'multishop' => true,
        'fields' => [
            'title' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'meta_title' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'meta_description' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'link_rewrite' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite',
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'excerpt' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'id_author' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'indexable' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'follow' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'sitemap' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'post_status' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isName',
                'required' => true,
            ],
            'id_default_category' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'allowed_groups' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false,
            ],
            'post_categories' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false,
            ],
            'post_tags' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false,
            ],
            'post_products' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false,
            ],
            'psswd' => [
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isPlaintextPassword',
            ],
            'starred' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'count' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
        ],
    ];

    /**
     * Get all posts depending on start and limit
     * @param int id_lang, int id_shop, int start query, int limit query, string post_status, bool is feed page or not
     * @return array of posts obj with changed shortcodes
    */
    public static function getPosts(
        $id_lang,
        $id_shop,
        $start = 0,
        $limit = null,
        $post_status = 'published',
        $is_feed = false,
        $starred = false,
        $orderBy = null,
        $orderWay = null
    ) {
        $cache_id = 'EverPsBlogPost::getPosts_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $start
        . '_'
        . (int) $limit
        . '_'
        . $post_status
        . '_'
        . $is_feed
        . '_'
        . $starred;
        if (!Cache::isStored($cache_id)) {
            if (!(int) $limit <= 0) {
                $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $context = Context::getContext();
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary']
            );
            $sql->leftJoin(
                self::$definition['table'] . '_shop',
                'bps',
                'bp.' . self::$definition['primary'] . ' = bps.' . self::$definition['primary']
                . ' AND bps.id_shop = ' . (int) $id_shop
            );
            $sql->where('bp.post_status = "' . pSQL($post_status) . '"');
            $sql->where('bpl.id_lang = ' . (int) $id_lang);
            if ((bool) $starred === true) {
                $sql->where('bp.starred = 1');
            }
            $sql->orderBy('bp.' . ($orderBy ? $orderBy : 'date_add') . ' ' . ($orderWay ? $orderWay : 'desc'));
            $sql->limit((int) $limit, (int) $start);
            $posts = Db::getInstance()->executeS($sql);
            $return = [];
            if ($context->controller->controller_type == 'front'
                || $context->controller->controller_type == 'modulefront'
            ) {
                foreach ($posts as $post) {
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    $post_category = new EverPsBlogCategory(
                        (int) $post['id_default_category'],
                        (int) $context->language->id,
                        (int) $context->shop->id
                    );
                    if (isset($post_category->allowed_groups)
                        && $post_category->allowed_groups
                    ) {
                        $allowedGroups = json_decode($post_category->allowed_groups);
                        if (isset($customerGroups)
                            && !empty($allowedGroups)
                            && !array_intersect($allowedGroups, $customerGroups)
                        ) {
                            continue;
                        }
                    }
                    if (isset($post['allowed_groups'])
                        && $post['allowed_groups']
                    ) {
                        $allowedGroups = json_decode($post['allowed_groups']);
                        if (isset($customerGroups)
                            && !empty($allowedGroups)
                            && !array_intersect($allowedGroups, $customerGroups)
                        ) {
                            continue;
                        }
                    }
                    $post['title'] = self::changeShortcodes(
                        $post['title'],
                        (int) $context->customer->id
                    );
                    $post['content'] = self::changeShortcodes(
                        $post['content'],
                        (int) $context->customer->id
                    );
                    $post['excerpt'] = self::changeShortcodes(
                        $post['excerpt'],
                        (int) $context->customer->id
                    );
                    $post['date_add'] = date('d/m/Y', strtotime($post['date_add']));
                    $post['date_upd'] = date('d/m/Y', strtotime($post['date_upd']));
                    if ((bool) $is_feed === false) {
                        // Length
                        $post['title'] = Tools::substr(
                            $post['title'],
                            0,
                            (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                        );
                        $post['content'] = Tools::substr(
                            strip_tags($post['content']),
                            0,
                            (int) Configuration::get('EVERPSBLOG_EXCERPT')
                        );
                        $post['excerpt'] = Tools::substr(
                            strip_tags($post['excerpt']),
                            0,
                            (int) Configuration::get('EVERPSBLOG_EXCERPT')
                        );
                    }
                    $post['featured_image'] = EverPsBlogImage::getBlogImageUrl(
                        (int) $post[self::$definition['primary']],
                        (int) $id_shop,
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

    /**
     * Get latest posts
     * @param int id_lang, int id_shop, int start query, int limit query, string post_status
     * @return array of posts obj with changed shortcodes
    */
    public static function getStarredPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::getStarredPosts_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $start
        . '_'
        . (int) $limit
        . '_'
        . $post_status;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            if ((int) $limit <= 0) {
                $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary']
            );
            $sql->leftJoin(
                self::$definition['table'] . '_shop',
                'bps',
                'bp.' . self::$definition['primary'] . ' = bps.' . self::$definition['primary']
                . ' AND bps.id_shop = ' . (int) $id_shop
            );
            $sql->where('bp.post_status = "' . pSQL($post_status) . '"');
            $sql->where('bp.starred = 1');
            $sql->where('bpl.id_lang = ' . (int) $id_lang);
            $sql->limit((int) $limit, (int) $start);
            $sql->orderBy('bp.date_add DESC');
            $sql->orderBy('bp.' . self::$definition['primary'] . ' DESC');
            $posts = Db::getInstance()->executeS($sql);
            $return = [];
            if (empty($posts)) {
                return static::getLatestPosts($id_lang, $id_shop, $start, $limit, $post_status);
            }
            foreach ($posts as $post_array) {
                if (isset($post_array['allowed_groups'])
                    && $post_array['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post_array['allowed_groups']);
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post_category = new EverPsBlogCategory(
                    (int) $post_array['id_default_category'],
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (isset($post_category->allowed_groups)
                    && $post_category->allowed_groups
                ) {
                    $allowedGroups = json_decode($post_category->allowed_groups);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post = new self(
                    (int) $post_array[self::$definition['primary']],
                    (int) $id_lang,
                    (int) $id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int) $context->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int) $context->customer->id
                );
                $post->excerpt = self::changeShortcodes(
                    $post->excerpt,
                    (int) $context->customer->id
                );
                $post->date_add = date('d/m/Y', strtotime($post->date_add));
                $post->date_upd = date('d/m/Y', strtotime($post->date_upd));
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    strip_tags($post->content),
                    0,
                    (int) Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->excerpt = Tools::substr(
                    strip_tags($post->excerpt),
                    0,
                    (int) Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int) $post->id,
                    (int) $id_shop,
                    'post'
                );
                $post_category = new EverPsBlogCategory(
                    (int) $post->id_default_category,
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (Validate::isLoadedObject($post_category)) {
                    $post->default_cat_obj = $post_category;
                }
                // TODO : add default category object as post property
                $return[] = $post;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get latest posts
     * @param int id_lang, int id_shop, int start query, int limit query, string post_status
     * @return array of posts obj with changed shortcodes
    */
    public static function getLatestPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::getLatestPosts_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $start
        . '_'
        . (int) $limit
        . '_'
        . $post_status;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            if ((int) $limit <= 0) {
                $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.post_status = "' . pSQL($post_status) . '"');
            $sql->where('bp.id_shop = ' . (int) $id_shop);
            $sql->where('bpl.id_lang = ' . (int) $id_lang);
            $sql->limit((int) $limit, (int) $start);
            $sql->orderBy('bp.date_add DESC');
            $sql->orderBy('bp.' . self::$definition['primary'] . ' DESC');
            $posts = Db::getInstance()->executeS($sql);
            $return = [];
            foreach ($posts as $post_array) {
                if (isset($post_array['allowed_groups'])
                    && $post_array['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post_array['allowed_groups']);
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post_category = new EverPsBlogCategory(
                    (int) $post_array['id_default_category'],
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (isset($post_category->allowed_groups)
                    && $post_category->allowed_groups
                ) {
                    $allowedGroups = json_decode($post_category->allowed_groups);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post = new self(
                    (int) $post_array[self::$definition['primary']],
                    (int) $id_lang,
                    (int) $id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int) $context->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int) $context->customer->id
                );
                $post->excerpt = self::changeShortcodes(
                    $post->excerpt,
                    (int) $context->customer->id
                );
                $post->date_add = date('d/m/Y', strtotime($post->date_add));
                $post->date_upd = date('d/m/Y', strtotime($post->date_upd));
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    strip_tags($post->content),
                    0,
                    (int) Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->excerpt = Tools::substr(
                    strip_tags($post->excerpt),
                    0,
                    (int) Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int) $post->id,
                    (int) $id_shop,
                    'post'
                );
                $post_category = new EverPsBlogCategory(
                    (int) $post->id_default_category,
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (Validate::isLoadedObject($post_category)) {
                    $post->default_cat_obj = $post_category;
                }
                // TODO : add default category object as post property
                $return[] = $post;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get all posts by tag
     * @param int id_lang, int id_shop,
     * int tag id, int start query, int limit query, string post_status, bool is feed page
     * @return array of posts obj with changed shortcodes
    */
    public static function getPostsByTag(
        $id_lang,
        $id_shop,
        $id_tag,
        $start = 0,
        $limit = null,
        $post_status = 'published',
        $is_feed = false,
        $starred = false
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByTag_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_tag
        . '_'
        . (int) $start
        . '_'
        . (int) $limit
        . '_'
        . $post_status
        . '_'
        . (bool) $is_feed
        . '_'
        . (bool) $starred;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            if (!(int) $limit <= 0) {
                $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->leftJoin(
                self::$definition['table'] . '_tag',
                'bpt',
                'bpt.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int) $id_shop);
            $sql->where('bpl.id_lang = '.(int) $id_lang);
            $sql->where('bpt.' . self::$definition['primary'] . '_tag = ' . (int) $id_tag);
            if ((bool) $starred === true) {
                $sql->where('bp.starred = 1');
            }
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int) $limit, (int) $start);
            $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            foreach ($posts as $post) {
                if (isset($post['allowed_groups'])
                    && $post['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post['allowed_groups']);
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post = new self(
                    (int) $post[self::$definition['primary']],
                    (int) $id_lang,
                    (int) $id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int) $context->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int) $context->customer->id
                );
                $post->excerpt = self::changeShortcodes(
                    $post->excerpt,
                    (int) $context->customer->id
                );
                $post->date_add = date('d/m/Y', strtotime($post->date_add));
                $post->date_upd = date('d/m/Y', strtotime($post->date_upd));
                if ((bool) $is_feed === false) {
                    // Length
                    $post->title = Tools::substr(
                        $post->title,
                        0,
                        (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                    );
                    $post->content = Tools::substr(
                        strip_tags($post->content),
                        0,
                        (int) Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                    $post->excerpt = Tools::substr(
                        strip_tags($post->excerpt),
                        0,
                        (int) Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                }
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int) $post->id,
                    (int) $id_shop,
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

    /**
     * Get all posts by category
     * @param int id_lang, int id_shop,
     * int category id, int start query, int limit query, string post_status, bool is feed page
     * @return array of posts obj with changed shortcodes
    */
    public static function getPostsByCategory(
        $id_lang,
        $id_shop,
        $id_category,
        $start = 0,
        $limit = null,
        $post_status = 'published',
        $is_feed = false,
        $starred = false
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByTag_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_category
        . '_'
        . (int) $start
        . '_'
        . (int) $limit
        . '_'
        . $post_status
        . '_'
        . $is_feed
        . '_'
        . $starred;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            if (!(int) $limit <= 0) {
                $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->from(self::$definition['table'], 'ebp');
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->leftJoin(
                self::$definition['table'] . '_category',
                'bpc',
                'bpc.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int) $id_shop);
            $sql->where('bpl.id_lang = '.(int) $id_lang);
            $sql->where('bpc.' . self::$definition['primary'] . '_category = '.(int) $id_category);
            if ((bool) $starred === true) {
                $sql->where('bp.starred = 1');
            }
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int) $limit, (int) $start);
            $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            foreach ($posts as $post_array) {
                if (isset($post_array['allowed_groups'])
                    && $post_array['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post_array['allowed_groups']);
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post = new self(
                    (int) $post_array[self::$definition['primary']],
                    (int) $id_lang,
                    (int) $id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int) $context->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int) $context->customer->id
                );
                $post->excerpt = self::changeShortcodes(
                    $post->excerpt,
                    (int) $context->customer->id
                );
                $post->date_add = date('d/m/Y', strtotime($post->date_add));
                $post->date_upd = date('d/m/Y', strtotime($post->date_upd));
                if ((bool) $is_feed === false) {
                    // Length
                    $post->title = Tools::substr(
                        $post->title,
                        0,
                        (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                    );
                    $post->content = Tools::substr(
                        strip_tags($post->content),
                        0,
                        (int) Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                    $post->excerpt = Tools::substr(
                        strip_tags($post->excerpt),
                        0,
                        (int) Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                }
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int) $post->id,
                    (int) $id_shop,
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

    /**
     * Get all posts by author
     * @param int id_lang, int id_shop,
     * int author id, int start query, int limit query, string post_status, bool is feed page
     * @return array of posts obj with changed shortcodes
    */
    public static function getPostsByAuthor(
        $id_lang,
        $id_shop,
        $id_author,
        $start = 0,
        $limit = null,
        $post_status = 'published',
        $is_feed = false,
        $starred = false
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByAuthor_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_author
        . '_'
        . (int) $start
        . '_'
        . (int) $limit
        . '_'
        . $post_status
        . '_'
        . (bool) $is_feed
        . '_'
        . (bool) $starred;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            if (!(int) $limit <= 0) {
                $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_author = '.(int) $id_author);
            $sql->where('bp.id_shop = '.(int) $id_shop);
            $sql->where('bpl.id_lang = '.(int) $id_lang);
            if ((bool) $starred === true) {
                $sql->where('bp.starred = 1');
            }
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int) $limit, (int) $start);
            $posts = Db::getInstance()->executeS($sql);
            $return = [];
            foreach ($posts as $post) {
                if (isset($post_array['allowed_groups'])
                    && $post_array['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post_array['allowed_groups']);
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post = new self(
                    (int) $post[self::$definition['primary']],
                    (int) $id_lang,
                    (int) $id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int) $context->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int) $context->customer->id
                );
                $post->excerpt = self::changeShortcodes(
                    $post->excerpt,
                    (int) $context->customer->id
                );
                $post->date_add = date('d/m/Y', strtotime($post->date_add));
                $post->date_upd = date('d/m/Y', strtotime($post->date_upd));
                if ((bool) $is_feed === false) {
                    // Length
                    $post->title = Tools::substr(
                        $post->title,
                        0,
                        (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                    );
                    $post->content = Tools::substr(
                        strip_tags($post->content),
                        0,
                        (int) Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                    $post->excerpt = Tools::substr(
                        strip_tags($post->excerpt),
                        0,
                        (int) Configuration::get('EVERPSBLOG_EXCERPT')
                    );
                }
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int) $post->id,
                    (int) $id_shop,
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

    /**
     * Get all posts by product
     * @param int id_lang, int id_shop, int id_product, int start query, int limit query, string post_status
     * @return array of posts obj with changed shortcodes
    */
    public static function getPostsByProduct(
        $id_lang,
        $id_shop,
        $id_product,
        $start = 0,
        $limit = null,
        $post_status = 'published'
    ) {
        $cache_id = 'EverPsBlogPost::getPostsByProduct_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_product
        . '_'
        . (int) $start
        . '_'
        . (int) $limit
        . '_'
        . $post_status;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            if (!(int) $limit <= 0) {
                $limit = (int) Configuration::get('EVERPSBLOG_PAGINATION');
            }
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->leftJoin(
                self::$definition['table'] . '_product',
                'bpp',
                'bpp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.post_status = "' . pSQL($post_status) . '"');
            $sql->where('bp.id_shop = ' . (int) $id_shop);
            $sql->where('bpl.id_lang = ' . (int) $id_lang);
            $sql->where('bpp.' . self::$definition['primary'] . '_product = ' . (int) $id_product);
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int) $limit, (int) $start);
            $posts = Db::getInstance()->executeS($sql);
            $return = [];
            foreach ($posts as $post_array) {
                if (isset($post_array['allowed_groups'])
                    && $post_array['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post_array['allowed_groups']);
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post = new self(
                    (int) $post_array[self::$definition['primary']],
                    (int) $id_lang,
                    (int) $id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int) $context->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int) $context->customer->id
                );
                $post->excerpt = self::changeShortcodes(
                    $post->content,
                    (int) $context->customer->id
                );
                $post->date_add = date('d/m/Y', strtotime($post->date_add));
                $post->date_upd = date('d/m/Y', strtotime($post->date_upd));
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    strip_tags($post->content),
                    0,
                    (int) Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->excerpt = Tools::substr(
                    strip_tags($post->excerpt),
                    0,
                    (int) Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $post->featured_image = EverPsBlogImage::getBlogImageUrl(
                    (int) $post->id,
                    (int) $id_shop,
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

    /**
     * Get all post categories
     * @param int post id, int id_shop, int id_lang, bool active or not
     * @return json array of post categories
    */
    public static function getPostCategories($id_ever_post, $id_shop, $id_lang, $active = true, $starred = false)
    {
        $cache_id = 'EverPsBlogPost::getPostCategories_'
        . (int) $id_ever_post
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_lang
        . '_'
        . (int) $active
        . '_'
        . (bool) $starred;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery();
            $sql->select('post_categories');
            $sql->from(self::$definition['table'], 'ep');
            $sql->leftJoin(
                self::$definition['table'] . '_lang',
                'bpl',
                'ep.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where(
                'ep.' . self::$definition['primary'] . ' = ' . (int) $id_ever_post
            );
            $sql->where(
                'ep.id_shop = ' . (int) $id_shop
            );
            $sql->where(
                'ep.id_lang = ' . (int) $id_lang
            );
            $sql->where(
                'ep.active = ' . (int) $active
            );
            if ((bool) $starred === true) {
                $sql->where('bp.starred = 1');
            }
            $sql->orderBy('ep.date_add DESC');
            $post_categories = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            $return = json_decode($post_categories);
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get post by link rewrite
     * @param string link rewrite
     * @return post obj
    */
    public static function getPostByLinkRewrite(
        $link_rewrite
    ) {
        $cache_id = 'EverPsBlogPost::getPostByLinkRewrite_'
        . $link_rewrite;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select(self::$definition['primary']);
            $sql->from(self::$definition['table'] . '_lang');
            $sql->where('link_rewrite = "'.pSQL($link_rewrite).'"');
            $id_ever_post = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            $return = new self(
                (int) $id_ever_post
            );
            if (isset($return->allowed_groups)
                && $return->allowed_groups
            ) {
                $allowedGroups = json_decode($return->allowed_groups);
                $customerGroups = Customer::getGroupsStatic(
                    (int) $context->customer->id
                );
                if (isset($customerGroups)
                    && !empty($allowedGroups)
                    && !array_intersect($allowedGroups, $customerGroups)
                ) {
                    $return = new self();
                }
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Count posts number
     * @param int id_lang, int id_shop, string post status
     * @return int posts count
    */
    public static function countPosts($id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPosts_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . $post_status;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.post_status = "' . pSQL($post_status) . '"');
            $sql->where('bp.id_shop = ' . (int) $id_shop);
            $sql->where('bpl.id_lang = ' . (int) $id_lang);
            $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $count = 0;
            foreach ($posts as $post) {
                $customerGroups = Customer::getGroupsStatic(
                    (int) $context->customer->id
                );
                if (isset($post['allowed_groups'])
                    && $post['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post['allowed_groups']);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post_category = new EverPsBlogCategory(
                    (int) $post['id_default_category'],
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (isset($post_category->allowed_groups)
                    && $post_category->allowed_groups
                ) {
                    $allowedGroups = json_decode($post_category->allowed_groups);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $count = $count + 1;
            }
            Cache::store($cache_id, $count);
            return $count;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Count post per tag
     * @param int tag id, int id_lang, int id_shop, string post status
     * @return int post count per tag
    */
    public static function countPostsByTag($id_tag, $id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPostsByTag_'
        . (int) $id_tag
        . '_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . $post_status;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->leftJoin(
                self::$definition['table'] . '_tag',
                'bpc',
                'bpc.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bpc.' . self::$definition['primary'] . '_tag = '.(int) $id_tag);
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = ' . (int) $id_shop);
            $sql->where('bpl.id_lang = ' . (int) $id_lang);
            $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $count = 0;
            foreach ($posts as $post) {
                if (isset($post['allowed_groups'])
                    && $post['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post['allowed_groups']);
                    $customerGroups = Customer::getGroupsStatic(
                        (int) $context->customer->id
                    );
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $count = $count + 1;
            }
            Cache::store($cache_id, $count);
            return $count;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Count post per category
     * @param int category id, int id_lang, int id_shop, string post status
     * @return int post count per category
    */
    public static function countPostsByCategory($id_category, $id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPostsByCategory_'
        . (int) $id_category
        . '_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . $post_status;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->leftJoin(
                self::$definition['table'] . '_category',
                'bpc',
                'bpc.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bpc.' . self::$definition['primary'] . '_category = '.(int) $id_category);
            $sql->where('bp.post_status = "'.pSQL($post_status).'"');
            $sql->where('bp.id_shop = '.(int) $id_shop);
            $sql->where('bpl.id_lang = '.(int) $id_lang);
            $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $count = 0;
            foreach ($posts as $post) {
                $customerGroups = Customer::getGroupsStatic(
                    (int) $context->customer->id
                );
                if (isset($post['allowed_groups'])
                    && $post['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post['allowed_groups']);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post_category = new EverPsBlogCategory(
                    (int) $post['id_default_category'],
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (isset($post_category->allowed_groups)
                    && $post_category->allowed_groups
                ) {
                    $allowedGroups = json_decode($post_category->allowed_groups);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $count = $count + 1;
            }
            Cache::store($cache_id, $count);
            return $count;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Count post per author
     * @param int author id, int id_lang, int id_shop, string post status
     * @return int post count per author
    */
    public static function countPostsByAuthor($id_author, $id_lang, $id_shop, $post_status = 'published')
    {
        $cache_id = 'EverPsBlogPost::countPostsByAuthor_'
        . (int) $id_author
        . '_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . $post_status;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.post_status = "' . pSQL($post_status) . '"');
            $sql->where('bp.id_author = ' . (int) $id_author);
            $sql->where('bp.id_shop = ' . (int) $id_shop);
            $sql->where('bpl.id_lang = ' . (int) $id_lang);
            $posts = Db::getInstance()->executeS($sql);
            $count = 0;
            foreach ($posts as $post) {
                $customerGroups = Customer::getGroupsStatic(
                    (int) $context->customer->id
                );
                if (isset($post['allowed_groups'])
                    && $post['allowed_groups']
                ) {
                    $allowedGroups = json_decode($post['allowed_groups']);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $post_category = new EverPsBlogCategory(
                    (int) $post['id_default_category'],
                    (int) $context->language->id,
                    (int) $context->shop->id
                );
                if (isset($post_category->allowed_groups)
                    && $post_category->allowed_groups
                ) {
                    $allowedGroups = json_decode($post_category->allowed_groups);
                    if (isset($customerGroups)
                        && !empty($allowedGroups)
                        && !array_intersect($allowedGroups, $customerGroups)
                    ) {
                        continue;
                    }
                }
                $count = $count + 1;
            }
            Cache::store($cache_id, $count);
            return $count;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Change string by replacing shortcodes
     * @param string message, int customer entity
     * @return string updated
    */
    public static function changeShortcodes($message, $id_entity = false)
    {
        $link = new Link();
        $contactLink = $link->getPageLink('contact');
        if (!$id_entity) {
            $id_entity = Context::getContext()->customer->id;
        }
        if ($id_entity && $id_entity > 0) {
            $entity = new Customer(
                (int) $id_entity
            );
            $gender = new Gender(
                (int) $entity->id_gender,
                (int) $entity->id_lang
            );
            $entityShortcodes = [
                '[entity_lastname]' => $entity->lastname,
                '[entity_firstname]' => $entity->firstname,
                '[entity_company]' => $entity->company,
                '[entity_siret]' => $entity->siret,
                '[entity_ape]' => $entity->ape,
                '[entity_birthday]' => $entity->birthday,
                '[entity_website]' => $entity->website,
                '[entity_gender]' => $gender->name,
            ];
        } else {
            $entityShortcodes = [
                '[entity_lastname]' => '',
                '[entity_firstname]' => '',
                '[entity_company]' => '',
                '[entity_siret]' => '',
                '[entity_ape]' => '',
                '[entity_birthday]' => '',
                '[entity_website]' => '',
                '[entity_gender]' => '',
            ];
        }
        $defaultShortcodes = [
            '[shop_url]' => Tools::getShopDomainSsl(true),
            '[shop_name]'=> (string)Configuration::get('PS_SHOP_NAME'),
            '[start_cart_link]' => '<a href="'
            . Tools::getShopDomainSsl(true)
            . '/index.php?controller=cart&action=show" rel="nofollow" target="_blank">',
            '[end_cart_link]' => '</a>',
            '[start_shop_link]' => '<a href="'
            . Tools::getShopDomainSsl(true)
            . '" target="_blank">',
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
        ];
        $shortcodes = array_merge($entityShortcodes, $defaultShortcodes);
        foreach ($shortcodes as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }

    /**
     * Drop post author on each post
     * @param int id author
     * @return bool if update query successful
    */
    public static function dropBlogAuthorPosts($id_ever_author)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . self::$definition['table'] . '
            SET id_author = 0
            WHERE id_author = ' . (int) $id_ever_author . ';
        ';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        } else {
            return true;
        }
    }

    public static function searchPost(
        $query,
        $id_shop,
        $id_lang,
        $start = 0,
        $limit = null,
        $is_feed = false
    ) {
        $cached_string = Tools::str2url(
            $query
        );
        $cache_id = 'EverPsBlogPost::searchPosts_'
        . $cached_string
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $id_lang
        . '_'
        . (int) $start
        . '_'
        . (int) $limit;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'] . '_lang', 'bpl');
            $sql->leftJoin(
                self::$definition['table'],
                'bp',
                'bp.' . self::$definition['primary'] . ' = bpl.' . self::$definition['primary'] . ''
            );
            $sql->where('bp.id_shop = '.(int) $id_shop);
            $sql->where('bpl.id_lang = '.(int) $id_lang);
            $sql->where('INSTR(title, "' . pSQL($query) . '") OR INSTR(content, "' . pSQL($query) . '")');
            $sql->orderBy('bp.date_add DESC');
            $sql->limit((int) $limit, (int) $start);
            $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $return = [];
            if ($context->controller->controller_type == 'front'
                || $context->controller->controller_type == 'modulefront'
            ) {
                foreach ($posts as $post) {
                    if (isset($post['allowed_groups'])
                        && $post['allowed_groups']
                    ) {
                        $allowedGroups = json_decode($post['allowed_groups']);
                        $customerGroups = Customer::getGroupsStatic(
                            (int) $context->customer->id
                        );
                        if (isset($customerGroups)
                            && !empty($allowedGroups)
                            && !array_intersect($allowedGroups, $customerGroups)
                        ) {
                            continue;
                        }
                    }
                    $post['title'] = self::changeShortcodes(
                        $post['title'],
                        (int) $context->customer->id
                    );
                    $post['content'] = self::changeShortcodes(
                        $post['content'],
                        (int) $context->customer->id
                    );
                    $post['excerpt'] = self::changeShortcodes(
                        $post['excerpt'],
                        (int) $context->customer->id
                    );
                    $post['date_add'] = date('d/m/Y', strtotime($post['date_add']));
                    $post['date_upd'] = date('d/m/Y', strtotime($post['date_upd']));
                    if ((bool) $is_feed === false) {
                        // Length
                        $post['title'] = Tools::substr(
                            $post['title'],
                            0,
                            (int) Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                        );
                        $post['content'] = Tools::substr(
                            strip_tags($post['content']),
                            0,
                            (int) Configuration::get('EVERPSBLOG_EXCERPT')
                        );
                        $post['excerpt'] = Tools::substr(
                            strip_tags($post['excerpt']),
                            0,
                            (int) Configuration::get('EVERPSBLOG_EXCERPT')
                        );
                    }
                    $post['featured_image'] = EverPsBlogImage::getBlogImageUrl(
                        (int) $post[self::$definition['primary']],
                        (int) $id_shop,
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

    public static function updatePostViewCount($id_ever_post, $id_shop)
    {
        $count =
            'SELECT count
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE ' . self::$definition['primary'] . ' = ' . (int) $id_ever_post . '
                AND id_shop = ' . (int) $id_shop;
        $currentCount = Db::getInstance()->getValue($count);
        $update = Db::getInstance()->update(
            self::$definition['table'],
            ['count' => (int) $currentCount + 1],
            self::$definition['primary'] . ' = ' . (int) $id_ever_post
        );

        return $update;
    }

    /**
     * Check if post password is the right one.
     *
     * @param string $passwordHash Password
     *
     * @return bool result
     */
    public static function checkPassword($idPost, $passwordHash)
    {
        $sql = new DbQuery();
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        $sql->where(self::$definition['primary'] . ' = ' . (int) $idPost);
        $sql->where('`psswd` = \'' . pSQL($passwordHash) . '\'');
        $sql->where('`post_status` = "published"');
        return (bool) Db::getInstance()->getValue($sql);
    }

    public function convertToPrettyBlock()
    {
        if (!Module::isInstalled('prettyblocks')
            || Module::isEnabled('prettyblocks')
        ) {
            return;
        }
        // Récupérer toutes les langues disponibles pour le shop
        $languages = Language::getLanguages(true, $this->id_shop);

        foreach ($languages as $language) {
            // Récupérer le post de blog pour la langue spécifique
            $blogPost = Db::getInstance()->getRow('
                SELECT p.id_ever_post, pl.id_lang, pl.content, pl.title 
                FROM ' . _DB_PREFIX_ . 'ever_blog_post p
                INNER JOIN ' . _DB_PREFIX_ . 'ever_blog_post_lang pl ON p.id_ever_post = pl.id_ever_post
                WHERE p.id_ever_post = ' . (int)$this->id_ever_post . ' 
                AND pl.id_lang = ' . (int)$language['id_lang'] . ' 
                AND p.id_shop = ' . (int)$this->id_shop
            );

            // Si le post n'existe pas pour cette langue, on passe à la langue suivante
            if (!$blogPost) {
                continue;
            }

            // Récupérer les informations du post pour cette langue
            $id_lang = (int) $blogPost['id_lang'];
            $content = $blogPost['content'];
            $metaTitle = $blogPost['title'];
            $defaultTemplate = 'module:prettyblocks/views/templates/blocks/custom_text/default.tpl';

            // Créer une zone spécifique pour le post et la langue
            $zoneName = 'displayPost' . $this->id_ever_post;

            // Créer un nouveau bloc PrettyBlocks pour ce post et langue
            $prettyBlock = new PrettyBlocksModel();
            $prettyBlock->id_shop = $this->id_shop;
            $prettyBlock->id_lang = $id_lang;
            $prettyBlock->code = 'prettyblocks_custom_text';
            $prettyBlock->name = $metaTitle;
            $prettyBlock->zone_name = $zoneName;
            $prettyBlock->template = $defaultTemplate;

            // Configuration du bloc avec le contenu pour cette langue
            $prettyBlock->config = json_encode([
                'content' => [
                    'type' => 'editor',
                    'label' => 'Content',
                    'default' => '<p> lorem ipsum </p>',
                    'force_default_value' => true,
                    'value' => $content,
                ],
            ]);
            $prettyBlock->default_params = json_encode([
                'container' => true,
                'force_full_width' => false,
                'load_ajax' => false,
                'is_cached' => false,
                'bg_color' => '',
                'paddings' => [
                    'desktop' => [
                        'auto' => 0,
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'use_custom_data' => false,
                    ],
                    'tablet' => [
                        'auto' => 0,
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'use_custom_data' => false,
                    ],
                    'mobile' => [
                        'auto' => 0,
                        'top' => null,
                        'right' => null,
                        'bottom' => null,
                        'left' => null,
                        'use_custom_data' => false,
                    ],
                ],
                'margins' => [
                    'desktop' => [
                        'auto' => 0,
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'use_custom_data' => false,
                    ],
                    'tablet' => [
                        'auto' => 0,
                        'top' => '',
                        'right' => '',
                        'bottom' => '',
                        'left' => '',
                        'use_custom_data' => false,
                    ],
                    'mobile' => [
                        'auto' => 0,
                        'top' => null,
                        'right' => null,
                        'bottom' => null,
                        'left' => null,
                        'use_custom_data' => false,
                    ],
                ],
            ]);

            // Sauvegarder le bloc PrettyBlock
            $prettyBlock->add();

            // Facultatif : Vider le contenu du post pour cette langue
            Db::getInstance()->execute('
                UPDATE ' . _DB_PREFIX_ . 'ever_blog_post_lang
                SET content = ""
                WHERE id_ever_post = ' . (int)$this->id_ever_post . ' AND id_lang = ' . (int) $id_lang
            );
        }
    }
}
