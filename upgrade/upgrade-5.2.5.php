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

function upgrade_module_5_2_5()
{
    set_time_limit(0);
    $result = true;
    $sql = [];
    $sql[] =
        'ALTER TABLE ' . _DB_PREFIX_ . 'ever_blog_post
         ADD `id_default_category` int(10) unsigned NOT NULL DEFAULT 1
         AFTER `id_author`;
    ';
    $sql[] =
        'ALTER TABLE ' . _DB_PREFIX_ . 'ever_blog_post_lang
         ADD `excerpt` varchar(255) DEFAULT NULL
         AFTER `content`;
    ';
    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }
    $result &= Configuration::updateValue('EVERPSBLOG_TYPE', 'Article');
    $result &= Configuration::updateValue('EVERBLOG_IMPORT_POST_STATE', 'published');
    $result &= Configuration::updateValue('EVERBLOG_CSS_FILE', 'default');
    $result &= Configuration::updateValue('EVERBLOG_SHOW_FEAT_CAT', true);
    $result &= Configuration::updateValue('EVERBLOG_SHOW_FEAT_TAG', true);
    return $result;
}
