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
// SQL
$sql = array();

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_post` (
        `id_ever_post` int(10) unsigned NOT NULL auto_increment,
        `id_shop` int(10) unsigned NOT NULL,
        `id_author` int(10) unsigned NOT NULL,
        `post_status` varchar(255) NOT NULL,
        `date_add` DATETIME DEFAULT NULL,
        `date_upd` DATETIME DEFAULT NULL,
        `index` int(1) unsigned DEFAULT NULL,
        `follow` int(1) unsigned DEFAULT NULL,
        `sitemap` int(1) unsigned DEFAULT NULL,
        `active` int(1) unsigned DEFAULT NULL,
        `post_categories` varchar(255) DEFAULT NULL,
        `post_tags` varchar(255) DEFAULT NULL,
        `post_products` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id_ever_post`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_post_lang` (
        `id_ever_post` int(10) unsigned NOT NULL,
        `title` varchar(255) NOT NULL,
        `meta_title` varchar(255) DEFAULT NULL,
        `meta_description` varchar(255) DEFAULT NULL,
        `link_rewrite` varchar(255) DEFAULT NULL,
        `content` text NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_ever_post`, `id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_category` (
        `id_ever_category` int(10) unsigned NOT NULL auto_increment,
        `id_parent_category` int(10) DEFAULT NULL,
        `active` int(10) DEFAULT NULL,
        `id_shop` int(10) unsigned NOT NULL,
        `date_add` DATETIME DEFAULT NULL,
        `date_upd` DATETIME DEFAULT NULL,
        `index` int(1) unsigned DEFAULT NULL,
        `follow` int(1) unsigned DEFAULT NULL,
        `sitemap` int(1) unsigned DEFAULT NULL,
        `active` int(1) unsigned DEFAULT NULL,
        `category_products` varchar(255) DEFAULT NULL,
        `is_root_category` int(1) unsigned DEFAULT NULL,
        PRIMARY KEY (`id_ever_category`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_category_lang` (
        `id_ever_category` int(10) unsigned NOT NULL,
        `title` varchar(255) NOT NULL,
        `meta_title` varchar(255) DEFAULT NULL,
        `meta_description` varchar(255) DEFAULT NULL,
        `link_rewrite` varchar(255) DEFAULT NULL,
        `content` text NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_ever_category`, `id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_tag` (
        `id_ever_tag` int(10) unsigned NOT NULL auto_increment,
        `active` int(10) DEFAULT NULL,
        `id_shop` int(10) unsigned NOT NULL,
        `date_add` DATETIME DEFAULT NULL,
        `date_upd` DATETIME DEFAULT NULL,
        `index` int(10) unsigned DEFAULT NULL,
        `follow` int(10) unsigned DEFAULT NULL,
        `sitemap` int(1) unsigned DEFAULT NULL,
        `active` int(1) unsigned DEFAULT NULL,
        `tag_products` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id_ever_tag`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_tag_lang` (
        `id_ever_tag` int(10) unsigned NOT NULL,
        `title` varchar(255) NOT NULL,
        `meta_title` varchar(255) DEFAULT NULL,
        `meta_description` varchar(255) DEFAULT NULL,
        `link_rewrite` varchar(255) DEFAULT NULL,
        `content` text NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_ever_tag`, `id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_comments` (
        `id_ever_comment` int(10) unsigned NOT NULL auto_increment,
        `id_ever_post` int(10) unsigned NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        `comment` text NOT NULL,
        `name` text NOT NULL,
        `user_email` text NOT NULL,
        `date_add` DATETIME DEFAULT NULL,
        `date_upd` DATETIME DEFAULT NULL,
        `active` int(10) DEFAULT NULL,
        PRIMARY KEY (`id_ever_comment`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_author` (
        `id_ever_author` int(10) unsigned NOT NULL auto_increment,
        `id_employee` int(10) unsigned NOT NULL,
        `id_shop` int(10) unsigned NOT NULL,
        `nickhandle` varchar(255) NOT NULL,
        `twitter` varchar(255) DEFAULT NULL,
        `facebook` varchar(255) DEFAULT NULL,
        `linkedin` varchar(255) DEFAULT NULL,
        `date_add` DATETIME DEFAULT NULL,
        `date_upd` DATETIME DEFAULT NULL,
        `index` int(10) unsigned DEFAULT NULL,
        `follow` int(10) unsigned DEFAULT NULL,
        `sitemap` int(1) unsigned DEFAULT NULL,
        `author_products` varchar(255) DEFAULT NULL,
        `active` int(10) unsigned DEFAULT NULL,
        PRIMARY KEY (`id_ever_author`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_author_lang` (
        `id_ever_author` int(10) unsigned NOT NULL,
        `meta_title` varchar(255) DEFAULT NULL,
        `meta_description` varchar(255) DEFAULT NULL,
        `link_rewrite` varchar(255) DEFAULT NULL,
        `content` text NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_ever_author`, `id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_post_category` (
        `id_ever_post_category` int(10) NOT NULL,
        `id_ever_post` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_ever_post`, `id_ever_post_category`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_post_tag` (
        `id_ever_post_tag` int(10) NOT NULL,
        `id_ever_post` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_ever_post`, `id_ever_post_tag`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ever_blog_post_product` (
        `id_ever_post_product` int(10) NOT NULL,
        `id_ever_post` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_ever_post`, `id_ever_post_product`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

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
    if (!Db::getInstance()->execute($s)) {
        return false;
    }
}
