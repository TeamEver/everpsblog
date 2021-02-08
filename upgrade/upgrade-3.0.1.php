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

function upgrade_module_3_0_1()
{
    set_time_limit(0);
    $result = false;
    // Hook before post init
    $beforeEverPostInitContent = Hook::getIdByName('beforeEverPostInitContent');
    if ($beforeEverPostInitContent) {
        $hook = new Hook((int)$beforeEverPostInitContent);
        $hook->name = 'actionBeforeEverPostInitContent';
        $result &= $hook->save();
    }
    // Hook category init
    $beforeEverCategoryInitContent = Hook::getIdByName('beforeEverCategoryInitContent');
    if ($beforeEverCategoryInitContent) {
        $hook = new Hook((int)$beforeEverCategoryInitContent);
        $hook->name = 'actionBeforeEverCategoryInitContent';
        $result &= $hook->save();
    }
    // Hook before tag init
    $beforeEverTagInitContent = Hook::getIdByName('beforeEverTagInitContent');
    if ($beforeEverTagInitContent) {
        $hook = new Hook((int)$beforeEverTagInitContent);
        $hook->name = 'actionBeforeEverTagInitContent';
        $result &= $hook->save();
    }
    // Hook before blog init content
    $beforeEverBlogInitContent = Hook::getIdByName('beforeEverBlogInitContent');
    if ($beforeEverBlogInitContent) {
        $hook = new Hook((int)$beforeEverBlogInitContent);
        $hook->name = 'actionBeforeEverBlogInitContent';
        $result &= $hook->save();
    }
    // Hook before blog init
    $beforeEverBlogInit = Hook::getIdByName('beforeEverBlogInit');
    if ($beforeEverBlogInit) {
        $hook = new Hook((int)$beforeEverBlogInit);
        $hook->name = 'actionBeforeEverBlogInit';
        $result &= $hook->save();
    }
    // Hook after blog init
    $afterEverBlogInit = Hook::getIdByName('afterEverBlogInit');
    if ($afterEverBlogInit) {
        $hook = new Hook((int)$afterEverBlogInit);
        $hook->name = 'actionAfterEverBlogInit';
        $result &= $hook->save();
    }
    // SQL alter and create
    $sql = array();
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_post
         ADD `id_author` int(10) unsigned NOT NULL
         AFTER `id_shop`
    ';
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

    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }
    // Author tab
    $tab = new Tab();
    $tab->active = 1;
    $tab->class_name = 'AdminEverPsBlogAuthor';
    $tab->id_parent = (int)Tab::getIdFromClassName('AdminEverPsBlog');
    $tab->position = Tab::getNewLastPosition($tab->id_parent);
    $tab->module = 'everpsblog';

    foreach (Language::getLanguages(false) as $lang) {
        $tab->name[(int)$lang['id_lang']] = 'Authors';
    }
    $result &= $tab->add();
    // Migrate all json datas to taxonomy
    require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTaxonomy.php';
    $result &= EverPsBlogTaxonomy::migrateJsonPostsData();

    return $result;
}
