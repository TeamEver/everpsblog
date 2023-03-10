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

function upgrade_module_5_0_1()
{
    require_once _PS_MODULE_DIR_ . 'everpsblog/classes/EverPsBlogImage.php';
    set_time_limit(0);
    $result = true;
    if (!file_exists(_PS_IMG_DIR_ . 'post')) {
        $result &= mkdir(_PS_IMG_DIR_ . 'post', 0755, true);
    }
    if (!file_exists(_PS_IMG_DIR_ . 'tag')) {
        $result &= mkdir(_PS_IMG_DIR_ . 'tag', 0755, true);
    }
    if (!file_exists(_PS_IMG_DIR_ . 'category')) {
        $result &= mkdir(_PS_IMG_DIR_ . 'category', 0755, true);
    }
    if (!file_exists(_PS_IMG_DIR_ . 'author')) {
        $result &= mkdir(_PS_IMG_DIR_ . 'author', 0755, true);
    }
    $featured_images = EverPsBlogImage::getAllBlogImages();
    foreach ($featured_images as $featured_image) {
        $exists = EverPsBlogImage::oldBlogFileExist(
            (int) $featured_image->id_element,
            $featured_image->image_type
        );
        if ((bool) $exists === true) {
            // First copy file
            switch ($featured_image->image_type) {
                case 'post':
                    $file = _PS_MODULE_DIR_
                    .'everpsblog/views/img/posts/post_image_'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    $destination = _PS_IMG_DIR_
                    .'post/'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    break;

                case 'category':
                    $file = _PS_MODULE_DIR_
                    .'everpsblog/views/img/categories/category_image_'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    $destination = _PS_IMG_DIR_
                    .'category/'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    break;

                case 'tag':
                    $file = _PS_MODULE_DIR_
                    .'everpsblog/views/img/tags/tag_image_'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    $destination = _PS_IMG_DIR_
                    .'tag/'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    break;

                case 'author':
                    $file = _PS_MODULE_DIR_
                    .'everpsblog/views/img/authors/author_image_'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    $destination = _PS_IMG_DIR_
                    .'author/'
                    .(int) $featured_image->id_element
                    .'.jpg';
                    break;
                
                default:
                    # code...
                    break;
            }
            copy($file, $destination);
            // Then update link
            $img_link = 'img/'
            .$featured_image->image_type
            .'/'
            .(int) $featured_image->id_element
            .'.jpg';
            $featured_image->image_link = $img_link;
            $result &= $featured_image->save();
            // At least drop file on module's folder
            $result &= unlink($file);
        }
    }
    // Drop all blog img files and folders
    if ((bool) $result === true) {
        $dir = _PS_MODULE_DIR_
        .'everpsblog/views/img/posts/';
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($dir.'/'.$object) == 'dir') {
                    $result &= rmdir($dir.'/'.$object);
                } else {
                    unlink($dir.'/'.$object);
                }
            }
        }
        $result &= rmdir($dir);
        $dir = _PS_MODULE_DIR_
        .'everpsblog/views/img/categories/';
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($dir.'/'.$object) == 'dir') {
                    $result &= rmdir($dir.'/'.$object);
                } else {
                    unlink($dir.'/'.$object);
                }
            }
        }
        $result &= rmdir($dir);
        $dir = _PS_MODULE_DIR_
        .'everpsblog/views/img/tags/';
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($dir.'/'.$object) == 'dir') {
                    $result &= rmdir($dir.'/'.$object);
                } else {
                    unlink($dir.'/'.$object);
                }
            }
        }
        $result &= rmdir($dir);
        $dir = _PS_MODULE_DIR_
        .'everpsblog/views/img/authors/';
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($dir.'/'.$object) == 'dir') {
                    $result &= rmdir($dir.'/'.$object);
                } else {
                    unlink($dir.'/'.$object);
                }
            }
        }
        $result &= rmdir($dir);
    }
    return $result;
}
