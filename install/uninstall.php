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
$sql = [];

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_post`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_shop`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_category`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_category_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_category_shop`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_tag`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_tag_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_tag_shop`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_comments`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_author`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_author_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_author_shop`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_image`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_image_shop`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_category`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_tag`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_product`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_category_product`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_tag_product`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ever_blog_author_product`';

foreach ($sql as $s) {
    if (!Db::getInstance()->execute($s)) {
        return false;
    }
}
