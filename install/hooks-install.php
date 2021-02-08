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
$result = false;
// Hook before post
if (!Hook::getIdByName('displayBeforeEverPost')) {
    $hook = new Hook();
    $hook->name = 'displayBeforeEverPost';
    $hook->title = 'Before ever post';
    $hook->description = 'This hook displays new elements before post';
    $result &= $hook->save();
}
// Hook after post
if (!Hook::getIdByName('displayAfterEverPost')) {
    $hook = new Hook();
    $hook->name = 'displayAfterEverPost';
    $hook->title = 'After ever post';
    $hook->description = 'This hook displays new elements after post';
    $result &= $hook->save();
}
// Hook before category
if (!Hook::getIdByName('displayBeforeEverCategory')) {
    $hook = new Hook();
    $hook->name = 'displayBeforeEverCategory';
    $hook->title = 'Before ever category';
    $hook->description = 'This hook displays new elements before category';
    $result &= $hook->save();
}
// Hook after category
if (!Hook::getIdByName('displayAfterEverCategory')) {
    $hook = new Hook();
    $hook->name = 'displayAfterEverCategory';
    $hook->title = 'After ever category';
    $hook->description = 'This hook displays new elements after category';
    $result &= $hook->save();
}
// Hook before tag
if (!Hook::getIdByName('displayBeforeEverTag')) {
    $hook = new Hook();
    $hook->name = 'displayBeforeEverTag';
    $hook->title = 'Before ever tag';
    $hook->description = 'This hook displays new elements before tag';
    $result &= $hook->save();
}
// Hook after tag
if (!Hook::getIdByName('displayAfterEverTag')) {
    $hook = new Hook();
    $hook->name = 'displayAfterEverTag';
    $hook->title = 'After ever tag';
    $hook->description = 'This hook displays new elements after tag';
    $result &= $hook->save();
}
// Hook before comment
if (!Hook::getIdByName('displayBeforeEverComment')) {
    $hook = new Hook();
    $hook->name = 'displayBeforeEverComment';
    $hook->title = 'Before ever comment';
    $hook->description = 'This hook displays new elements before comment';
    $result &= $hook->save();
}
// Hook after comment
if (!Hook::getIdByName('displayAfterEverComment')) {
    $hook = new Hook();
    $hook->name = 'displayAfterEverComment';
    $hook->title = 'After ever comment';
    $hook->description = 'This hook displays new elements after comment';
    $result &= $hook->save();
}
// Hook before loop
if (!Hook::getIdByName('displayBeforeEverLoop')) {
    $hook = new Hook();
    $hook->name = 'displayBeforeEverLoop';
    $hook->title = 'Before ever loop';
    $hook->description = 'This hook displays new elements before loop';
    $result &= $hook->save();
}
// Hook after loop
if (!Hook::getIdByName('displayAfterEverLoop')) {
    $hook = new Hook();
    $hook->name = 'displayAfterEverLoop';
    $hook->title = 'After ever loop';
    $hook->description = 'This hook displays new elements after loop';
    $result &= $hook->save();
}
// Hook before post init
if (!Hook::getIdByName('actionBeforeEverPostInitContent')) {
    $hook = new Hook();
    $hook->name = 'actionBeforeEverPostInitContent';
    $hook->title = 'Before post init';
    $hook->description = 'This hook is triggered before post init';
    $result &= $hook->save();
}
// Hook before category init
if (!Hook::getIdByName('actionBeforeEverCategoryInitContent')) {
    $hook = new Hook();
    $hook->name = 'actionBeforeEverCategoryInitContent';
    $hook->title = 'Before category init';
    $hook->description = 'This hook is triggered before category init';
    $result &= $hook->save();
}
// Hook before tag init
if (!Hook::getIdByName('actionBeforeEverTagInitContent')) {
    $hook = new Hook();
    $hook->name = 'actionBeforeEverTagInitContent';
    $hook->title = 'Before tag init';
    $hook->description = 'This hook is triggered before tag init';
    $result &= $hook->save();
}
// Hook before blog init
if (!Hook::getIdByName('actionBeforeEverBlogInitContent')) {
    $hook = new Hook();
    $hook->name = 'actionBeforeEverBlogInitContent';
    $hook->title = 'Before blog init';
    $hook->description = 'This hook is triggered before blog init';
    $result &= $hook->save();
}
// Hook before blog init
if (!Hook::getIdByName('actionBeforeEverBlogInit')) {
    $hook = new Hook();
    $hook->name = 'actionBeforeEverBlogInit';
    $hook->title = 'Before blog init';
    $hook->description = 'This hook is triggered before main blog init';
    $result &= $hook->save();
}
// Hook after blog init
if (!Hook::getIdByName('actionAfterEverBlogInit')) {
    $hook = new Hook();
    $hook->name = 'actionAfterEverBlogInit';
    $hook->title = 'After blog init';
    $hook->description = 'This hook is triggered after main blog init';
    $result &= $hook->save();
}
return $result;
