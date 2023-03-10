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

function upgrade_module_4_4_1()
{
    set_time_limit(0);
    $result = false;
    // Preparing new taxonomies
    $sql = [];
    $sql[] =
        'ALTER TABLE ' . _DB_PREFIX_ . 'ever_blog_category_lang
         ADD `bottom_content` text DEFAULT NULL
         AFTER `content`;
    ';
    $sql[] =
        'ALTER TABLE ' . _DB_PREFIX_ . 'ever_blog_tag_lang
         ADD `bottom_content` text DEFAULT NULL
         AFTER `content`;
    ';
    $sql[] =
        'ALTER TABLE ' . _DB_PREFIX_ . 'ever_blog_author_lang
         ADD `bottom_content` text DEFAULT NULL
         AFTER `content`;
    ';

    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }
    return $result;
}
