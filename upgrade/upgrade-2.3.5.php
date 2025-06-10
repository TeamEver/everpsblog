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

function upgrade_module_2_3_5()
{
    set_time_limit(0);
    $module = Module::getInstanceByName('everpsblog');
    $result = false;
    // Hook before post init
    if (!Hook::getIdByName('beforeEverPostInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeEverPostInitContent';
        $hook->title = 'Before post init';
        $hook->description = 'This hook is triggered before post init';
        $result &= $hook->save();
        $result &= $module->registerHook($hook->name);
    }
    // Hook before category init
    if (!Hook::getIdByName('beforeEverCategoryInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeEverCategoryInitContent';
        $hook->title = 'Before category init';
        $hook->description = 'This hook is triggered before category init';
        $result &= $hook->save();
        $result &= $module->registerHook($hook->name);
    }
    // Hook before tag init
    if (!Hook::getIdByName('beforeEverTagInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeTagInitContent';
        $hook->title = 'Before tag init';
        $hook->description = 'This hook is triggered before tag init';
        $result &= $hook->save();
        $result &= $module->registerHook($hook->name);
    }
    // Hook before blog init
    if (!Hook::getIdByName('beforeEverBlogInitContent')) {
        $hook = new Hook();
        $hook->name = 'beforeEverBlogInitContent';
        $hook->title = 'Before blog init';
        $hook->description = 'This hook is triggered before blog init';
        $result &= $hook->save();
        $result &= $module->registerHook($hook->name);
    }
    return $result;
}
