<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @link http://www.team-ever.com
 * @copyright Teamm Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_'))
    exit;

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
    public $date_add;
    public $date_upd;
    public $post_status;
    public $post_categories;
    public $post_tags;
    public $post_products;
    public $index;
    public $follow;
    public $limit;

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
                'validate' => 'isString'
            ),
            'content' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false
            ),
            'id_shop' => array(
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
        )
    );

    public static function getPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
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
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->limit((int)$limit, (int)$start);
        $posts = Db::getInstance()->executeS($sql);
        return $posts;
    }

    public static function getLatestPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
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
        return $posts;
    }

    public static function getPostsByTag($id_lang, $id_shop, $id_tag, $start = 0, $limit = null, $post_status = 'published')
    {
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
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->limit((int)$limit, (int)$start);
        $posts = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($posts as $post) {
            if (in_array($id_tag, json_decode($post['post_tags']))) {
                $return[] = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
            }
        }
        if ($return) {
            return $return;
        }
    }

    public static function getPostsByCategory($id_lang, $id_shop, $id_category, $start = 0, $limit = null, $post_status = 'published')
    {
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
        $posts = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($posts as $post) {
            if (in_array($id_category, json_decode($post['post_categories']))) {
                $return[] = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
            }
        }
        // die(var_dump($return));
        if ($return) {
            return $return;
        }
    }

    public static function getPostsByProduct($id_lang, $id_shop, $id_product, $start = 0, $limit = null, $post_status = 'published')
    {
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
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->orderBy('bp.date_add DESC');
        $sql->orderBy('bp.id_ever_post DESC');
        $sql->limit((int)$limit, (int)$start);
        $posts = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($posts as $post) {
            if (in_array($id_product, json_decode($post['post_products']))) {
                $return[] = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
            }
        }
        if ($return) {
            return $return;
        }
    }

    public static function getPostCategories($id_ever_post, $id_shop, $id_lang, $active = true)
    {
        $sql = new DbQuery();
        $sql->select('post_categories');
        $sql->from('ever_blog_post', 'ep');
        $sql->where(
            'ep.id_ever_post = '.(int)$id_ever_post
        );
        $sql->where(
            'ep.id_shop = '.(int)$id_shop
        );
        $sql->where(
            'ep.active = '.(int)$active
        );
        $sql->limit((int)$limit);
        $post_categories = Db::getInstance()->getValue($sql);
        return json_decode($post_categories);
    }

    public static function countPosts($id_lang, $id_shop, $post_status = 'published')
    {
        $sql = new DbQuery;
        $sql->select('COUNT(*)');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $count = Db::getInstance()->getValue($sql);
        if ($count) {
            return (int)$count;
        }
    }

    public static function countPostsByTag($id_tag, $id_lang, $id_shop, $post_status = 'published')
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $posts = Db::getInstance()->executeS($sql);
        $count = 0;
        foreach ($posts as $post) {
            if (in_array($id_tag, json_decode($post['post_tags']))) {
                $count += 1;
            }
        }
        if ($count) {
            return $count;
        }
    }

    public static function countPostsByCategory($id_category, $id_lang, $id_shop, $post_status = 'published')
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $posts = Db::getInstance()->executeS($sql);
        $count = 0;
        foreach ($posts as $post) {
            if (in_array($id_category, json_decode($post['post_categories']))) {
                $count += 1;
            }
        }
        if ($count) {
            return $count;
        }
    }
}
