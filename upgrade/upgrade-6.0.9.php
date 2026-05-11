<?php

declare(strict_types=1);


if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_9()
{
    $module = \Module::getInstanceByName('everpsblog');
    if (!\Validate::isLoadedObject($module)) {
        return false;
    }

    $result = true;
    if (method_exists($module, 'checkAndFixDatabase')) {
        if (!(bool) $module->checkAndFixDatabase()) {
            $result = false;
        }
    }

    $result = (bool) $module->registerHook('actionDispatcherBefore') && $result;

    try {
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearCache')) {
            \Tools::clearCache();
        }
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearSmartyCache')) {
            \Tools::clearSmartyCache();
        }
    } catch (\Throwable $exception) {
        \PrestaShopLogger::addLog('[everpsblog][upgrade-6.0.9] ' . $exception->getMessage(), 2);
    }

    return (bool) $result;
}
