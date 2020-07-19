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

function upgrade_module_2_3_8()
{
    set_time_limit(0);
    $module = Module::getInstanceByName('everpsblog');
    $result = false;
    // Hook before blog init
    if (!Hook::getIdByName('beforeEverBlogInit')) {
        $hook = new Hook();
        $hook->name = 'beforeEverBlogInit';
        $hook->title = 'Before blog init';
        $hook->description = 'This hook is triggered before main blog init';
        $result &= $hook->save();
        $result &= $module->registerHook($hook->name);
    }
    // Hook after blog init
    if (!Hook::getIdByName('afterEverBlogInit')) {
        $hook = new Hook();
        $hook->name = 'afterEverBlogInit';
        $hook->title = 'After blog init';
        $hook->description = 'This hook is triggered after main blog init';
        $result &= $hook->save();
        $result &= $module->registerHook($hook->name);
    }
    return $result;
}
