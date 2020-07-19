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
// Hook before post
$displayBeforeEverPost = Hook::getIdByName('displayBeforeEverPost');
if ($displayBeforeEverPost) {
    $hook = new Hook((int)$displayBeforeEverPost);
    $hook->delete();
}
// Hook after post
$displayAfterEverPost = Hook::getIdByName('displayAfterEverPost');
if ($displayAfterEverPost) {
    $hook = new Hook((int)$displayAfterEverPost);
    $hook->delete();
}
// Hook before category
$displayBeforeEverCategory = Hook::getIdByName('displayBeforeEverCategory');
if ($displayBeforeEverCategory) {
    $hook = new Hook((int)$displayBeforeEverCategory);
    $hook->delete();
}
// Hook after category
$displayAfterEverCategory = Hook::getIdByName('displayAfterEverCategory');
if ($displayAfterEverCategory) {
    $hook = new Hook((int)$displayAfterEverCategory);
    $hook->delete();
}
// Hook before tag
$displayBeforeEverTag = Hook::getIdByName('displayBeforeEverTag');
if ($displayBeforeEverTag) {
    $hook = new Hook((int)$displayBeforeEverTag);
    $hook->delete();
}
// Hook after tag
$displayAfterEverTag = Hook::getIdByName('displayAfterEverTag');
if ($displayAfterEverTag) {
    $hook = new Hook((int)$displayAfterEverTag);
    $hook->delete();
}
// Hook before comment
$displayBeforeEverComment = Hook::getIdByName('displayBeforeEverComment');
if ($displayBeforeEverComment) {
    $hook = new Hook((int)$displayBeforeEverComment);
    $hook->delete();
}
// Hook after comment
$displayAfterEverComment = Hook::getIdByName('displayAfterEverComment');
if ($displayAfterEverComment) {
    $hook = new Hook((int)$displayAfterEverComment);
    $hook->delete();
}
// Hook before loop
$displayBeforeEverLoop = Hook::getIdByName('displayBeforeEverLoop');
if ($displayBeforeEverLoop) {
    $hook = new Hook((int)$displayBeforeEverLoop);
    $hook->delete();
}
// Hook after loop
$displayAfterEverLoop = Hook::getIdByName('displayAfterEverLoop');
if ($displayAfterEverLoop) {
    $hook = new Hook((int)$displayAfterEverLoop);
    $hook->delete();
}
// Hook before post init
$actionBeforeEverPostInitContent = Hook::getIdByName('actionBeforeEverPostInitContent');
if ($actionBeforeEverPostInitContent) {
    $hook = new Hook((int)$actionBeforeEverPostInitContent);
    $hook->delete();
}
// Hook category init
$actionBeforeEverCategoryInitContent = Hook::getIdByName('actionBeforeEverCategoryInitContent');
if ($actionBeforeEverCategoryInitContent) {
    $hook = new Hook((int)$actionBeforeEverCategoryInitContent);
    $hook->delete();
}
// Hook before tag init
$actionBeforeEverTagInitContent = Hook::getIdByName('actionBeforeEverTagInitContent');
if ($actionBeforeEverTagInitContent) {
    $hook = new Hook((int)$actionBeforeEverTagInitContent);
    $hook->delete();
}
// Hook before blog init content
$actionBeforeEverBlogInitContent = Hook::getIdByName('actionBeforeEverBlogInitContent');
if ($actionBeforeEverBlogInitContent) {
    $hook = new Hook((int)$actionBeforeEverBlogInitContent);
    $hook->delete();
}
// Hook before blog init
$actionBeforeEverBlogInit = Hook::getIdByName('actionBeforeEverBlogInit');
if ($actionBeforeEverBlogInit) {
    $hook = new Hook((int)$actionBeforeEverBlogInit);
    $hook->delete();
}
// Hook after blog init
$actionAfterEverBlogInit = Hook::getIdByName('actionAfterEverBlogInit');
if ($actionAfterEverBlogInit) {
    $hook = new Hook((int)$actionAfterEverBlogInit);
    $hook->delete();
}
