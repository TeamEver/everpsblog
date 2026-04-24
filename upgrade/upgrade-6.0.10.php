<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_10()
{
    $module = \Module::getInstanceByName('everpsblog');
    if (!\Validate::isLoadedObject($module)) {
        return false;
    }

    $result = true;
    $emptyTrashDelay = \Configuration::get('EVERBLOG_EMPTY_TRASH');
    if (false === $emptyTrashDelay || null === $emptyTrashDelay || '' === $emptyTrashDelay) {
        $result = (bool) \Configuration::updateValue('EVERBLOG_EMPTY_TRASH', 7) && $result;
    }

    if (\Hook::getIdByName('actionOutputHTMLBefore')) {
        $module->unregisterHook('actionOutputHTMLBefore');
    }

    try {
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearCache')) {
            \Tools::clearCache();
        }
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearSmartyCache')) {
            \Tools::clearSmartyCache();
        }
    } catch (\Throwable $exception) {
        \PrestaShopLogger::addLog('[everpsblog][upgrade-6.0.10] ' . $exception->getMessage(), 2);
    }

    return (bool) $result;
}
