<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_0_1()
{
    $module = \Module::getInstanceByName('everpsblog');
    if (!\Validate::isLoadedObject($module)) {
        return false;
    }

    $result = (bool) $module->registerHook('actionAdminControllerSetMedia');

    if (\Hook::getIdByName('filterQcdPageBuilderBackOfficeTargets')) {
        $result = (bool) $module->registerHook('filterQcdPageBuilderBackOfficeTargets') && $result;
    }

    try {
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearCache')) {
            \Tools::clearCache();
        }
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearSmartyCache')) {
            \Tools::clearSmartyCache();
        }
    } catch (\Throwable $exception) {
        \PrestaShopLogger::addLog('[everpsblog][upgrade-7.0.1] ' . $exception->getMessage(), 2);
    }

    return (bool) $result;
}
