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

function upgrade_module_2_3_5()
{
    set_time_limit(0);
    $result = false;
    // Hook before post init
    if (!Hook::getIdByName('beforeEverPostInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeEverPostInitContent';
        $hook->title = 'Before post init';
        $hook->description = 'This hook is triggered before post init';
        $result &= $hook->save();
    }
    // Hook before category init
    if (!Hook::getIdByName('beforeEverCategoryInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeEverCategoryInitContent';
        $hook->title = 'Before category init';
        $hook->description = 'This hook is triggered before category init';
        $result &= $hook->save();
    }
    // Hook before tag init
    if (!Hook::getIdByName('beforeEverTagInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeTagInitContent';
        $hook->title = 'Before tag init';
        $hook->description = 'This hook is triggered before tag init';
        $result &= $hook->save();
    }
    // Hook before blog init
    if (!Hook::getIdByName('beforeEverBlogInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeEverBlogInitContent';
        $hook->title = 'Before blog init';
        $hook->description = 'This hook is triggered before blog init';
        $result &= $hook->save();
    }
    return $result;
}
