<?php

declare(strict_types=1);

$prestashopRoot = dirname(__DIR__, 2);
$rootAutoload = $prestashopRoot . '/autoload.php';

if (is_file($rootAutoload)) {
    require_once $rootAutoload;
}

$moduleMainFile = __DIR__ . '/everpsblog.php';
if (is_file($moduleMainFile)) {
    require_once $moduleMainFile;
}

if (!defined('_PS_ROOT_DIR_')) {
    define('_PS_ROOT_DIR_', $prestashopRoot);
}

if (!defined('_PS_MODULE_DIR_')) {
    define('_PS_MODULE_DIR_', _PS_ROOT_DIR_ . '/modules/');
}

if (!defined('_PS_IMG_DIR_')) {
    define('_PS_IMG_DIR_', _PS_ROOT_DIR_ . '/img/');
}

if (!defined('_PS_IMG_')) {
    define('_PS_IMG_', _PS_IMG_DIR_);
}

if (!defined('_DB_PREFIX_')) {
    define('_DB_PREFIX_', 'ps_');
}

if (!defined('_MYSQL_ENGINE_')) {
    define('_MYSQL_ENGINE_', 'InnoDB');
}

if (!defined('_PS_USE_SQL_SLAVE_')) {
    define('_PS_USE_SQL_SLAVE_', false);
}

if (!defined('__PS_BASE_URI__')) {
    define('__PS_BASE_URI__', '/');
}

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '8.0.0');
}

if (!defined('_COOKIE_KEY_')) {
    define('_COOKIE_KEY_', 'phpstan-cookie-key');
}

if (!defined('_PS_VERSION_')) {
    exit;
}
