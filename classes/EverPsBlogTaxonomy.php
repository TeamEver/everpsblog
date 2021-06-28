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

class EverPsBlogTaxonomy extends ObjectModel
{
    public $id_obj;
    public $id_post;
    public $obj_name;
    public $id_product;
    public $id_category;
    public $id_tag;

    /**
     * Insert new taxonomy from table
     *
     * @param $id_obj, id_post, $obj_name
     * @return false is error
     */
    public static function insertTaxonomy($id_obj, $id_post, $obj_name)
    {
        switch ($obj_name) {
            case 'category':
                $table = _DB_PREFIX_.'ever_blog_post_category';
                $key = 'id_ever_post_category';
                break;

            case 'tag':
                $table = _DB_PREFIX_.'ever_blog_post_tag';
                $key = 'id_ever_post_tag';
                break;

            case 'product':
                $table = _DB_PREFIX_.'ever_blog_post_product';
                $key = 'id_ever_post_product';
                break;
        }
        if (isset($table)
            && !empty($table)
            && isset($key)
            && !empty($key)
            && self::taxonomyExists($id_obj, $obj_name)
        ) {
            set_time_limit(0);
            $sql =
                'INSERT IGNORE INTO `'.pSQL($table).'` (
                    '.pSQL($key).',
                    id_ever_post
                )
                VALUES (
                    '.(int)$id_obj.',
                    '.(int)$id_post.'
                )';
            if (!Db::getInstance()->execute($sql)) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * Drop taxonomy from table
     *
     * @param id_post, $obj_name
     * @return false is error
     */
    public static function dropTaxonomy($id_post, $obj_name)
    {
        switch ($obj_name) {
            case 'category':
                $table = _DB_PREFIX_.'ever_blog_post_category';
                $key = 'id_ever_post_category';
                break;

            case 'tag':
                $table = _DB_PREFIX_.'ever_blog_post_tag';
                $key = 'id_ever_post_tag';
                break;

            case 'product':
                $table = _DB_PREFIX_.'ever_blog_post_product';
                $key = 'id_ever_post_product';
                break;
        }
        if (isset($table) && !empty($table) && isset($key) && !empty($key)) {
            set_time_limit(0);
            $sql = 'DELETE FROM '.pSQL($table).'
            WHERE id_ever_post = '.(int)$id_post;
            // If dropped, return insert as kind of update
            if (!Db::getInstance()->Execute($sql)) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * Update taxonomy from table
     *
     * @param $id_obj, id_post, $obj_name
     * @return insert taxonomy
     */
    public static function updateTaxonomy($id_obj, $id_post, $obj_name)
    {
        if ((bool)self::dropTaxonomy($id_post, $obj_name) === true) {
            return (bool)self::insertTaxonomy($id_obj, $id_post, $obj_name);
        }
    }

    /**
     * Drop product taxonomy from table
     *
     * @param $id_product
     * @return false is error
     */
    public static function dropProductTaxonomy($id_product)
    {
        set_time_limit(0);
        $sql = 'DELETE FROM `'._DB_PREFIX_.'ever_blog_post_product`
        WHERE id_ever_post_product = '.(int)$id_product.'
        ';
        if (!Db::getInstance()->Execute($sql)) {
            return false;
        }
    }

    /**
     * Drop category taxonomy from table
     *
     * @param $id_category
     * @return false is error
     */
    public static function dropCategoryTaxonomy($id_category)
    {
        set_time_limit(0);
        $sql = 'DELETE FROM `'._DB_PREFIX_.'ever_blog_post_category`
        WHERE id_ever_post_category = '.(int)$id_category.'
        ';
        if (!Db::getInstance()->Execute($sql)) {
            return false;
        }
    }

    /**
     * Drop tag taxonomy from table
     *
     * @param $id_tag
     * @return false is error
     */
    public static function dropTagTaxonomy($id_tag)
    {
        set_time_limit(0);
        $sql = 'DELETE FROM `'._DB_PREFIX_.'ever_blog_post_tag`
        WHERE id_ever_post_tag = '.(int)$id_tag.'
        ';
        if (!Db::getInstance()->Execute($sql)) {
            return false;
        }
    }

    /**
     * Get post tag taxonomies from table
     *
     * @param $id_post
     * @return array of all tag taxonomies for given post id
     */
    public static function getPostTagsTaxonomies($id_post)
    {
        $cache_id = 'EverPsBlogTaxonomy::getPostTagsTaxonomies_'
        .(int)$id_post;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_post_tag');
            $sql->from('ever_blog_post_tag');
            $sql->where('id_ever_post = '.(int)$id_post);
            $taxonomies = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $taxonomies);
            return $taxonomies;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get post category taxonomies from table
     *
     * @param $id_post
     * @return array of all category taxonomies for given post id
     */
    public static function getPostCategoriesTaxonomies($id_post)
    {
        $cache_id = 'EverPsBlogTaxonomy::getPostCategoriesTaxonomies_'
        .(int)$id_post;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('epc.id_ever_post_category');
            $sql->from('ever_blog_post_category', 'epc');
            $sql->leftJoin(
                'ever_blog_category',
                'bc',
                'bc.id_ever_category = epc.id_ever_post_category'
            );
            $sql->where('epc.id_ever_post = '.(int)$id_post);
            $sql->orderBy('bc.id_parent_category ASC');
            $sql->groupBy('bc.id_ever_category');
            $taxonomies = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $taxonomies);
            return $taxonomies;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get all parent category taxonomies for given id category
     * @param int category id, bool only active categories
     * @return array of all category obj
     */
    public static function getCategoryParentsTaxonomy($id_category, $active = 1)
    {
        $cache_id = 'EverPsBlogTaxonomy::getCategoryParentsTaxonomy_'
        .(int)$id_category
        .'_'
        .(int)$active;
        if (!Cache::isStored($cache_id)) {
            $taxonomies = array();
            $root_category = EverPsBlogCategory::getRootCategory();
            $sql = new DbQuery;
            $sql->select('id_parent_category');
            $sql->from('ever_blog_category');
            $sql->where('id_ever_category = '.(int)$id_category);
            $sql->where('active = '.(int)$active);
            $taxonomy = Db::getInstance()->getValue($sql);
            if (isset($taxonomy) && (int)$taxonomy > 0) {
                $taxonomies[] = $taxonomy;
                $category = new self(
                    (int)$taxonomy
                );
                if ((int)$category->id_parent_category > 0
                    && (int)$root_category->id != (int)$category->id_parent_category) {
                    $taxonomies[] = (int)$category->id_parent_category;
                }
            }
            Cache::store($cache_id, $taxonomies);
            return $taxonomies;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostHighestCategory($id_post)
    {
        $cache_id = 'EverPsBlogTaxonomy::getPostHighestCategory_'
        .(int)$id_post;
        if (!Cache::isStored($cache_id)) {
            $root_category = EverPsBlogCategory::getRootCategory();
            $sql = new DbQuery;
            $sql->from('ever_blog_post_category');
            $sql->select('id_ever_post_category');
            $sql->where('id_ever_post = '.(int)$id_post);
            $sql->where('id_ever_post_category != '.(int)$root_category->id);
            $sql->orderBy('id_ever_post_category DESC');
            $taxonomies = Db::getInstance()->getValue($sql);
            Cache::store($cache_id, $taxonomies);
            return $taxonomies;
        }
        return Cache::retrieve($cache_id);
    }

    public static function getPostProductsTaxonomies($id_post)
    {
        $cache_id = 'EverPsBlogTaxonomy::getPostProductsTaxonomies_'
        .(int)$id_post;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->from('ever_blog_post_product');
            $sql->select('id_ever_post_product');
            $sql->where('id_ever_post = '.(int)$id_post);
            $taxonomies = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $taxonomies);
            return $taxonomies;
        }
        return Cache::retrieve($cache_id);
    }

    public static function checkDefaultPostCategory($id_post)
    {
        $taxonomies = self::getPostCategoriesTaxonomies($id_post);
        if (!empty($taxonomies) || count($taxonomies) <= 0) {
            $root_category = EverPsBlogCategory::getRootCategory();
            self::insertTaxonomy(
                $root_category->id,
                $id_post,
                'category'
            );
        }
    }

    /**
     * Chec if taxonomy exists
     *
     * @param int taxonomy, string taxonomy name
     * @return array of all category taxonomies for given post id
     */
    public static function taxonomyExists($id_obj, $obj_name)
    {
        $cache_id = 'EverPsBlogTaxonomy::taxonomyExists_'
        .(int)$id_obj
        .'_'
        .$obj_name;
        if (!Cache::isStored($cache_id)) {
            switch ($obj_name) {
                case 'category':
                    $table = 'ever_blog_category';
                    $key = 'id_ever_category';
                    break;

                case 'tag':
                    $table = 'ever_blog_tag';
                    $key = 'id_ever_tag';
                    break;

                case 'product':
                    $table = 'product';
                    $key = 'id_product';
                    break;
            }
            if (isset($table) && !empty($table) && isset($key) && !empty($key)) {
                $sql = new DbQuery;
                $sql->select(pSQL($key));
                $sql->from(pSQL($table));
                $sql->where(pSQL($key).' = '.(int)$id_obj);
                $return = Db::getInstance()->getValue($sql);
                Cache::store($cache_id, $return);
                return $return;
            }
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Migrate old datas from json
     *
     * @param $id_tag
     * @return false is error
     * @deprecated deprecated since version 5.0.1
     */
    public static function migrateJsonPostsData()
    {
        set_time_limit(0);
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post');
        $posts = Db::getInstance()->executeS($sql);
        foreach ($posts as $post_array) {
            $post = new EverPsBlogPost(
                (int)$post_array['id_ever_post']
            );
            $post_categories = json_decode(
                $post->post_categories
            );
            foreach ($post_categories as $post_category) {
                self::insertTaxonomy(
                    (int)$post_category,
                    (int)$post->id,
                    'category'
                );
            }
            $post_tags = json_decode(
                $post->post_tags
            );
            foreach ($post_tags as $post_tag) {
                self::insertTaxonomy(
                    (int)$post_tag,
                    (int)$post->id,
                    'tag'
                );
            }
            $post_products = json_decode(
                $post->post_products
            );
            foreach ($post_products as $post_product) {
                self::insertTaxonomy(
                    (int)$post_product,
                    (int)$post->id,
                    'product'
                );
            }
        }
    }
}
