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

class EverPsBlogCategory extends ObjectModel
{
    public $id_ever_category;
    public $title;
    public $meta_title;
    public $meta_description;
    public $link_rewrite;
    public $content;
    public $bottom_content;
    public $date_add;
    public $date_upd;
    public $id_parent_category;
    public $id_lang;
    public $id_shop;
    public $active;
    public $index;
    public $follow;
    public $sitemap;
    public $category_products;
    public $is_root_category;
    public $count;

    public static $definition = array(
        'table' => 'ever_blog_category',
        'primary' => 'id_ever_category',
        'multilang' => true,
        'fields' => array(
            'title' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString'
            ),
            'meta_title' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString'
            ),
            'meta_description' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString'
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
            'bottom_content' => array(
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
            'id_parent_category' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
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
                'required' => false
            ),
            'follow' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
            'sitemap' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
            'category_products' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
            'is_root_category' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
            'count' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
        )
    );

    public static function getRootCategory()
    {
        $cache_id = 'EverPsBlogCategory::getRootCategory';
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_category');
            $sql->from('ever_blog_category');
            $sql->where('is_root_category = 1');
            $return = Db::getInstance()->getValue($sql);
            $return = new self($return);
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public function hasParentCategory($id_parent_category)
    {
        $cache_id = 'EverPsBlogCategory::hasParentCategory_'
        .(int)$id_parent_category;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_category');
            $sql->from('ever_blog_category');
            $sql->where('id_ever_category = '.(int)$id_parent_category);
            $return = Db::getInstance()->getValue($sql);
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getAllCategories($id_lang, $id_shop, $active = 1, $except = 0)
    {
        $cache_id = 'EverPsBlogCategory::getAllCategories_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$active
        .'_'
        .(int)$except;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_category_lang', 'bcl');
            $sql->leftJoin(
                'ever_blog_category',
                'bc',
                'bc.id_ever_category = bcl.id_ever_category'
            );
            $sql->where('bc.active = '.(int)$active);
            $sql->where('bc.id_shop = '.(int)$id_shop);
            $sql->where('bcl.id_lang = '.(int)$id_lang);
            if ((int)$except > 0) {
                $sql->where('bc.id_ever_category != '.(int)$except);
                $sql->where('bc.id_parent_category != '.(int)$except);
            }
            $categories = Db::getInstance()->executeS($sql);
            $return = array();
            foreach ($categories as $blog_cat) {
                $blog_cat['featured_image'] = EverPsBlogImage::getBlogImageUrl(
                    (int)$blog_cat['id_ever_category'],
                    (int)$id_shop,
                    'category'
                );
                $return[] = $blog_cat;
            }
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getParentCategories($id_ever_category, $id_lang, $id_shop, $active = 1)
    {
        $cache_id = 'EverPsBlogCategory::getParentCategories_'
        .(int)$id_ever_category
        .'_'
        .(int)$id_lang
        .'_'
        .(int)$id_shop
        .'_'
        .(int)$active;
        if (!Cache::isStored($cache_id)) {
            $category = new self((int)$id_ever_category);
            $sql = new DbQuery;
            $sql->select('bc.*, bcl.*');
            $sql->from('ever_blog_category_lang', 'bcl');
            $sql->leftJoin(
                'ever_blog_category',
                'bc',
                'bc.id_ever_category = bcl.id_ever_category'
            );
            $sql->where('bc.active = '.(int)$active);
            $sql->where('bc.id_shop = '.(int)$id_shop);
            $sql->where('bcl.id_lang = '.(int)$id_lang);
            $sql->where('bc.id_parent_category = '.(int)$category->id_parent_category);
            $return = Db::getInstance()->executeS($sql);
            $categories = array();
            if (!isset($return[0])) {
                $root = EverPsBlogCategory::getRootCategory();
                $categories[] = array(
                    'id_ever_category' => $root->id,
                    'id_parent_category' => $root->id_parent_category,
                    'title' => $root->title,
                    'active' => $root->active
                );
            }
            if (!$return || $return[0]['id_parent_category'] == 0) {
                Cache::store($cache_id, $categories);
                return $categories;
            }
        }
        return Cache::retrieve($cache_id);
    }

    public static function getCategoryByLinkRewrite($link_rewrite)
    {
        $cache_id = 'EverPsBlogCategory::getCategoryByLinkRewrite_'
        .(string)$link_rewrite;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_category');
            $sql->from('ever_blog_category_lang');
            $sql->where('link_rewrite = "'.pSQL($link_rewrite).'"');
            $id_cat = Db::getInstance()->getValue($sql);
            if ($id_cat) {
                $return = new self($id_cat);
                Cache::store($cache_id, $return);
                return $return;
            }
            Cache::store($cache_id, false);
            return false;
        }
        return Cache::retrieve($cache_id);
    }
}
