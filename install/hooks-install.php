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
