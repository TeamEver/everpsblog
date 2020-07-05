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
$beforeEverPostInitContent = Hook::getIdByName('beforeEverPostInitContent');
if ($beforeEverPostInitContent) {
    $hook = new Hook((int)$beforeEverPostInitContent);
    $hook->delete();
}
// Hook category init
$beforeEverCategoryInitContent = Hook::getIdByName('beforeEverCategoryInitContent');
if ($beforeEverCategoryInitContent) {
    $hook = new Hook((int)$beforeEverCategoryInitContent);
    $hook->delete();
}
// Hook before tag init
$beforeEverTagInitContent = Hook::getIdByName('beforeEverTagInitContent');
if ($beforeEverTagInitContent) {
    $hook = new Hook((int)$beforeEverTagInitContent);
    $hook->delete();
}
// Hook before blog init
$beforeEverBlogInitContent = Hook::getIdByName('beforeEverBlogInitContent');
if ($beforeEverBlogInitContent) {
    $hook = new Hook((int)$beforeEverBlogInitContent);
    $hook->delete();
}
