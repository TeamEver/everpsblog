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

function upgrade_module_3_2_2()
{
    set_time_limit(0);
    $result = false;
    // Preparing new taxonomies
    $sql = array();
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_post
         ADD `sitemap` int(1) DEFAULT 1
         AFTER `follow`
    ';
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_tag
         ADD `sitemap` int(1) DEFAULT 1
         AFTER `follow`
    ';
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_category
         ADD `sitemap` int(1) DEFAULT 1
         AFTER `follow`
    ';
    $sql[] =
        'ALTER TABLE '._DB_PREFIX_.'ever_blog_author
         ADD `sitemap` int(1) DEFAULT 1
         AFTER `follow`
    ';

    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }

    return $result;
}
