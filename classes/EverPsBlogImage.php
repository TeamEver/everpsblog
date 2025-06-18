<?php
/**
 * 2019-2025 Team Ever
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
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsBlogImage extends ObjectModel
{
    public $id_ever_image;
    public $id_element;
    public $image_type;
    public $image_link;
    public $id_shop;

    public static $definition = [
        'table' => 'ever_blog_image',
        'primary' => 'id_ever_image',
        'multilang' => false,
        'multishop' => true,
        'fields' => [
            'id_element' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'image_type' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
            ],
            'image_link' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
        ],
    ];

    /**
     * Get all image types for blog
     * @return array of image types
    */
    public static function getImageTypes()
    {
        $image_types = [
            'post',
            'category',
            'tag',
            'author',
        ];
        return $image_types;
    }

    /**
     * Get all blog images
     * @return array of objects of all blog images
    */
    public static function getAllBlogImages()
    {
        $cache_id = 'EverPsBlogImage::getAllBlogImages';
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('ever_blog_image');
            $images = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $featured_images = [];
            foreach ($images as $image) {
                $featured_images[] = new self(
                    (int) $image['id_ever_image']
                );
            }
            Cache::store($cache_id, $featured_images);
            return $featured_images;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get blog image per element
     * @param int id_element, int id_shop, string image_type
     * @return array of objects of all blog images
     * @see getImageTypes()
    */
    public static function getBlogImage($id_element, $id_shop, $image_type)
    {
        $cache_id = 'EverPsBlogImage::getBlogImage_'
        . (int) $id_element
        . '_'
        . (int) $id_shop
        . '_'
        . $image_type;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('id_ever_image');
            $sql->from('ever_blog_image');
            $sql->where('id_element = ' . (int) $id_element);
            $sql->where('id_shop = ' . (int) $id_shop);
            $sql->where('image_type = "' . pSQL($image_type) . '"');
            $id_image = Db::getInstance()->getValue($sql);
            $image = new self(
                (int) $id_image
            );
            if (Validate::isLoadedObject($image)) {
                Cache::store($cache_id, $image);
                return $image;
            }
            Cache::store($cache_id, false);
            return false;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get blog image link
     * @param int id_element, int id_shop, string image_type
     * @return string image link
     * @see getImageTypes()
    */
    public static function getBlogImageUrl($id_element, $id_shop, $image_type)
    {
        $cache_id = 'EverPsBlogImage::getBlogImageUrl_'
        . (int) $id_element
        . '_'
        . (int) $id_shop
        . '_'
        . $image_type;
        if (!Cache::isStored($cache_id)) {
            $image = self::getBlogImage(
                (int) $id_element,
                (int) $id_shop,
                $image_type
            );
            if (!Validate::isLoadedObject($image)) {
                // If image is not set, return logo
                $return = Tools::getHttpHost(true)
                . __PS_BASE_URI__
                . '/img/'
                . Configuration::get(
                    'PS_LOGO'
                );
                Cache::store($cache_id, $return);
                return $return;
            }
            // Fix img bug on wrong migration
            switch ($image_type) {
                case 'post':
                    if (strpos($image->image_link, 'img/') !== true) {
                        $image->image_link = str_replace('posts/', 'img/post/', $image->image_link);
                    }
                    $image->save();
                    break;

                case 'category':
                    if (strpos($image->image_link, 'img/') !== true) {
                        $image->image_link = str_replace('categories/', 'img/category/', $image->image_link);
                    }
                    $image->save();
                    break;

                case 'author':
                    if (strpos($image->image_link, 'img/') !== true) {
                        $image->image_link = str_replace('authors/', 'img/author/', $image->image_link);
                    }
                    $image->save();
                    break;

                case 'tag':
                    if (strpos($image->image_link, 'img/') !== true) {
                        $image->image_link = str_replace('tags/', 'img/tag/', $image->image_link);
                    }
                    $image->save();
                    break;
                
                default:
                    # code...
                    break;
            }
            // Return file URL
            $return = Tools::getHttpHost(true)
            . __PS_BASE_URI__
            . $image->image_link;
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get resized blog image link
     * @param int $id_element
     * @param int $id_shop
     * @param string $image_type
     * @param int $width
     * @param int $height
     * @return string image link
     */
    public static function getBlogThumbUrl($id_element, $id_shop, $image_type, $width = 320, $height = 180)
    {
        $cache_id = 'EverPsBlogImage::getBlogThumbUrl_'
        . (int) $id_element
        . '_' . (int) $id_shop
        . '_' . $image_type
        . '_' . (int) $width
        . 'x' . (int) $height;
        if (!Cache::isStored($cache_id)) {
            $extension = 'jpg';
            $original = _PS_IMG_DIR_ . $image_type . '/' . (int) $id_element . '.' . $extension;

            if (!file_exists($original)) {
                $image = self::getBlogImage((int) $id_element, (int) $id_shop, $image_type);
                if (Validate::isLoadedObject($image)) {
                    $source = _PS_ROOT_DIR_ . '/' . ltrim($image->image_link, '/');
                    if (file_exists($source)) {
                        $extension = pathinfo($source, PATHINFO_EXTENSION);
                        $original = _PS_IMG_DIR_ . $image_type . '/' . (int) $id_element . '.' . $extension;
                        if (!file_exists($original)) {
                            Tools::copy($source, $original);
                        }
                    }
                }
            }

            if (!file_exists($original)) {
                $return = self::getBlogImageUrl($id_element, $id_shop, $image_type);
                Cache::store($cache_id, $return);
                return $return;
            }

            $thumbDir = _PS_IMG_DIR_ . $image_type . '/thumbs/';
            if (!file_exists($thumbDir)) {
                @mkdir($thumbDir, 0755, true);
            }

            $thumbFile = $thumbDir . (int) $id_element . '-' . (int) $width . 'x' . (int) $height . '.' . $extension;
            if (!file_exists($thumbFile)) {
                ImageManager::resize($original, $thumbFile, (int) $width, (int) $height);
            }

            $return = Tools::getHttpHost(true)
            . __PS_BASE_URI__
            . 'img/' . $image_type . '/thumbs/'
            . (int) $id_element . '-' . (int) $width . 'x' . (int) $height . '.' . $extension;
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Check if image exists on PS folder
     * @param int id_element, string image_type
     * @return bool file exists or not
     * @see getImageTypes()
    */
    public static function blogFileExist($id_element, $image_type)
    {
        $file = _PS_IMG_DIR_
        . $image_type.'/'
        . (int) $id_element
        . '.jpg';
        if (file_exists($file)) {
            return true;
        }
        return false;
    }

    /**
     * Check if image exists on old blog folders
     * @param int id_element, string image_type
     * @return bool file exists or not
     * @see getImageTypes()
     * @deprecated deprecated since version 5.0.1
    */
    public static function oldBlogFileExist($id_element, $image_type)
    {
        switch ($image_type) {
            case 'post':
                $file = _PS_MODULE_DIR_
                . 'everpsblog/views/img/posts/post_image_'
                . (int) $id_element
                . '.jpg';
                break;
            
            case 'category':
                $file = _PS_MODULE_DIR_
                . 'everpsblog/views/img/posts/post_image_'
                . (int) $id_element
                . '.jpg';
                break;
            
            case 'tag':
                $file = _PS_MODULE_DIR_
                . 'everpsblog/views/img/posts/post_image_'
                . (int) $id_element
                . '.jpg';
                break;
            
            case 'author':
                $file = _PS_MODULE_DIR_
                . 'everpsblog/views/img/posts/post_image_'
                . (int) $id_element
                . '.jpg';
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

    /**
     * Migrate all posts featured image files to database to old blog image system
     * @param int id_shop
     * @return bool if migration has been successfully passed
     * @deprecated deprecated since version 5.0.1
    */
    public static function migratePostsImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post');
        $posts = Db::getInstance()->executeS($sql);
        $result = false;
        foreach ($posts as $post) {
            $exists = self::getBlogImage(
                (int) $post['id_ever_post'],
                (int) $id_shop,
                'post'
            );
            if (!Validate::isLoadedObject($exists) && self::oldBlogFileExist($post['id_ever_post'], 'post')) {
                $featured_image = new self();
                $featured_image->id_element = $post['id_ever_post'];
                $featured_image->image_type = 'post';
                $featured_image->image_link = 'posts/post_image_'
                . (int) $post['id_ever_post']
                . '.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }

    /**
     * Migrate all categories featured images files to database to old blog image system
     * @param int id_shop
     * @return bool if migration has been successfully passed
     * @deprecated deprecated since version 5.0.1
    */
    public static function migrateCategoriesImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_category');
        $categories = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result = false;
        foreach ($categories as $category) {
            $exists = self::getBlogImage(
                (int) $category['id_ever_category'],
                (int) $id_shop,
                'category'
            );
            if (!Validate::isLoadedObject($exists)
                && self::oldBlogFileExist($category['id_ever_category'], 'category')
            ) {
                $featured_image = new self();
                $featured_image->id_element = $category['id_ever_category'];
                $featured_image->image_type = 'category';
                $featured_image->image_link = 'categories/category_image_'
                . (int) $category['id_ever_category']
                . '.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }

    /**
     * Migrate all tags featured images files to database to old blog image system
     * @param int id_shop
     * @return bool if migration has been successfully passed
     * @deprecated deprecated since version 5.0.1
    */
    public static function migrateTagsImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_tag');
        $tags = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result = false;
        foreach ($tags as $tag) {
            $exists = self::getBlogImage(
                (int) $tag['id_ever_tag'],
                (int) $id_shop,
                'tag'
            );
            if (!Validate::isLoadedObject($exists) && self::oldBlogFileExist($tag['id_ever_tag'], 'tag')) {
                $featured_image = new self();
                $featured_image->id_element = $tag['id_ever_tag'];
                $featured_image->image_type = 'tag';
                $featured_image->image_link = 'tags/tag_image_'
                . (int) $tag['id_ever_tag']
                . '.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }

    /**
     * Migrate all authors featured images files to database to old blog image system
     * @param int id_shop
     * @return bool if migration has been successfully passed
     * @deprecated deprecated since version 5.0.1
    */
    public static function migrateAuthorsImages($id_shop)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_author');
        $authors = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result = false;
        foreach ($authors as $author) {
            $exists = self::getBlogImage(
                (int) $author['id_ever_author'],
                (int) $id_shop,
                'author'
            );
            if (!Validate::isLoadedObject($exists) && self::oldBlogFileExist($author['id_ever_author'], 'author')) {
                $featured_image = new self();
                $featured_image->id_element = $author['id_ever_author'];
                $featured_image->image_type = 'author';
                $featured_image->image_link = 'authors/author_image_'
                . (int) $author['id_ever_author']
                . '.jpg';
                $result &= $featured_image->save();
            }
        }
        return $result;
    }
}
