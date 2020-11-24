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
 *  @copyright 2019-2020 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_9()
{
    set_time_limit(0);
    $result = false;
    // Preparing new taxonomies
    $sql = array();
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_category
         ADD `category_products` varchar(255) DEFAULT NULL
         AFTER `follow`
    ';
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_tag
         ADD `tag_products` varchar(255) DEFAULT NULL
         AFTER `follow`
    ';
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_author
         ADD `author_products` varchar(255) DEFAULT NULL
         AFTER `follow`
    ';
    $sql[] =
        'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_category_product` (
            `id_ever_category_product` int(10) NOT NULL,
            `id_ever_category` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_ever_category`, `id_ever_category_product`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    $sql[] =
        'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_tag_product` (
            `id_ever_tag_product` int(10) NOT NULL,
            `id_ever_tag` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_ever_tag`, `id_ever_tag_product`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    $sql[] =
        'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_author_product` (
            `id_ever_author_product` int(10) NOT NULL,
            `id_ever_author` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_ever_author`, `id_ever_author_product`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }

    return $result;
}
