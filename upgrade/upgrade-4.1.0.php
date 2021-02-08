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

function upgrade_module_4_1_0()
{
    require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogImage.php';
    set_time_limit(0);
    $result = false;
    // Preparing new taxonomies
    $sql = array();
    $sql[] =
        'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_image` (
            `id_ever_image` int(10) unsigned NOT NULL auto_increment,
            `image_type` varchar(255) DEFAULT NULL,
            `image_link` varchar(255) DEFAULT NULL,
            `id_element` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_ever_image`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }
    $shops = Db::getInstance()->executeS('SELECT id_shop FROM `'._DB_PREFIX_.'shop`');

    foreach ($shops as $shop) {
        $result &= EverPsBlogImage::migratePostsImages(
            (int)$shop['id_shop']
        );
        $result &= EverPsBlogImage::migrateCategoriesImages(
            (int)$shop['id_shop']
        );
        $result &= EverPsBlogImage::migrateTagsImages(
            (int)$shop['id_shop']
        );
        $result &= EverPsBlogImage::migrateAuthorsImages(
            (int)$shop['id_shop']
        );
    }
    return $result;
}
