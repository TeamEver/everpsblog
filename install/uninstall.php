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
$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_post`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_post_lang`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_category`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_category_lang`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_tag`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_tag_lang`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_comments`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_author`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_author_lang`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_post_category`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_post_tag`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ever_blog_post_product`';

foreach ($sql as $s) {
    if (!Db::getInstance()->execute($s)) {
        return false;
    }
}
