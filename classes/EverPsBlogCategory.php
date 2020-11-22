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

class EverPsBlogCategory extends ObjectModel
{
    public $id_ever_category;
    public $title;
    public $meta_title;
    public $meta_description;
    public $link_rewrite;
    public $content;
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
        )
    );

    public static function getRootCategory()
    {
        $sql = new DbQuery;
        $sql->select('id_ever_category');
        $sql->from('ever_blog_category');
        $sql->where('is_root_category = 1');
        $return = Db::getInstance()->getValue($sql);
        return new self($return);
    }

    public function hasParentCategory($id_parent_category)
    {
        $sql = new DbQuery;
        $sql->select('id_ever_category');
        $sql->from('ever_blog_category');
        $sql->where('id_ever_category = '.(int)$id_parent_category);
        $return = Db::getInstance()->getValue($sql);
        return $return;
    }

    public static function getAllCategories($id_lang, $id_shop, $active = 1, $except = 0)
    {
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
        $return = Db::getInstance()->executeS($sql);
        return $return;
    }

    public static function getParentCategories($id_ever_category, $id_lang, $id_shop, $active = 1)
    {
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
            return $categories;
        }
    }
}
