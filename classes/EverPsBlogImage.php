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

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCleaner.php';

class EverPsBlogImage extends ObjectModel
{
    public $id_ever_image;
    public $id_element;
    public $image_type;
    public $image_link;
    public $id_shop;

    public static $definition = array(
        'table' => 'ever_blog_image',
        'primary' => 'id_ever_image',
        'multilang' => false,
        'fields' => array(
            'id_element' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
            'image_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'image_link' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
        )
    );

    public static function getImageTypes()
    {
        $image_types = array(
            'post',
            'category',
            'tag',
            'author'
        );
        return $image_types;
    }

    public static function getBlogImage($id_element, $id_shop, $image_type)
    {
        $sql = new DbQuery;
        $sql->select('id_ever_image');
        $sql->from('ever_blog_image');
        $sql->where('id_element = '.(int)$id_element);
        $sql->where('id_shop = '.(int)$id_shop);
        $sql->where('image_type = "'.pSQL($image_type).'"');
        $id_image = Db::getInstance()->getValue($sql);
        $image = new self(
            (int)$id_image
        );
        if (Validate::isLoadedObject($image)) {
            return $image;
        }
        return false;
    }

    public static function getBlogImageUrl($id_element, $id_shop, $image_type)
    {
        $image = self::getBlogImage(
            (int)$id_element,
            (int)$id_shop,
            $image_type
        );
        if (!Validate::isLoadedObject($image)) {
            // If image is not set, return logo
            return Tools::getHttpHost(true)
            .__PS_BASE_URI__
            .'/img/'
            .Configuration::get(
                'PS_LOGO'
            );
        }
        // Return file URL
        return Tools::getHttpHost(true)
        .__PS_BASE_URI__
        .'modules/everpsblog/views/img/'
        .$image->image_link;
    }

    public static function blogFileExist($id_element, $image_type)
    {
        switch ($image_type) {
            case 'post':
                $file = _PS_MODULE_DIR_
                .'everpsblog/views/img/posts/post_image_'
                .(int)$id_element
                .'.jpg';
                break;
            
            case 'category':
                $file = _PS_MODULE_DIR_
                .'everpsblog/views/img/posts/post_image_'
                .(int)$id_element
                .'.jpg';
                break;
            
            case 'tag':
                $file = _PS_MODULE_DIR_
                .'everpsblog/views/img/posts/post_image_'
                .(int)$id_element
                .'.jpg';
                break;
            
            case 'author':
                $file = _PS_MODULE_DIR_
                .'everpsblog/views/img/posts/post_image_'
                .(int)$id_element
                .'.jpg';
                break;

            default:
                # code...
                break;
        }
        if (file_exists($file)) {
            return true;
        }
        return false;
    }

    public static function migratePostsImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post');
        $posts = Db::getInstance()->executeS($sql);
        $result = false;
        foreach ($posts as $post) {
            $exists = self::getBlogImage(
                (int)$post['id_ever_post'],
                (int)$id_shop,
                'post'
            );
            if (!Validate::isLoadedObject($exists) && self::blogFileExist($post['id_ever_post'], 'post')) {
                $featured_image = new self();
                $featured_image->id_element = $post['id_ever_post'];
                $featured_image->image_type = 'post';
                $featured_image->image_link = 'posts/post_image_'
                .(int)$post['id_ever_post']
                .'.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }

    public static function migrateCategoriesImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_category');
        $categories = Db::getInstance()->executeS($sql);
        $result = false;
        foreach ($categories as $category) {
            $exists = self::getBlogImage(
                (int)$category['id_ever_category'],
                (int)$id_shop,
                'category'
            );
            if (!Validate::isLoadedObject($exists) && self::blogFileExist($category['id_ever_category'], 'category')) {
                $featured_image = new self();
                $featured_image->id_element = $category['id_ever_category'];
                $featured_image->image_type = 'category';
                $featured_image->image_link = 'categories/category_image_'
                .(int)$category['id_ever_category']
                .'.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }

    public static function migrateTagsImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_tag');
        $tags = Db::getInstance()->executeS($sql);
        $result = false;
        foreach ($tags as $tag) {
            $exists = self::getBlogImage(
                (int)$tag['id_ever_tag'],
                (int)$id_shop,
                'tag'
            );
            if (!Validate::isLoadedObject($exists) && self::blogFileExist($tag['id_ever_tag'], 'tag')) {
                $featured_image = new self();
                $featured_image->id_element = $tag['id_ever_tag'];
                $featured_image->image_type = 'tag';
                $featured_image->image_link = 'tags/tag_image_'
                .(int)$tag['id_ever_tag']
                .'.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }

    public static function migrateAuthorsImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_author');
        $authors = Db::getInstance()->executeS($sql);
        $result = false;
        foreach ($authors as $author) {
            $exists = self::getBlogImage(
                (int)$author['id_ever_author'],
                (int)$id_shop,
                'author'
            );
            if (!Validate::isLoadedObject($exists) && self::blogFileExist($author['id_ever_author'], 'author')) {
                $featured_image = new self();
                $featured_image->id_element = $author['id_ever_author'];
                $featured_image->image_type = 'author';
                $featured_image->image_link = 'authors/author_image_'
                .(int)$author['id_ever_author']
                .'.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }
}
