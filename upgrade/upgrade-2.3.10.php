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
