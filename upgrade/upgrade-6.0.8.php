<?php

declare(strict_types=1);


if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * 6.0.8 ajoute l'action "Dupliquer" (ligne + action groupée) dans le grid BO Articles.
 * Aucun schéma DB à modifier : on force juste la régénération du cache Symfony pour que
 * les nouvelles routes `everpsblog_admin_post_duplicate` et `everpsblog_admin_post_bulk`
 * soient prises en compte.
 */
function upgrade_module_6_0_8()
{
    try {
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearCache')) {
            \Tools::clearCache();
        }
        if (class_exists('\\Tools') && method_exists('\\Tools', 'clearSmartyCache')) {
            \Tools::clearSmartyCache();
        }

        $cacheDirs = [
            _PS_CACHE_DIR_ . 'appcache',
            _PS_CACHE_DIR_ . 'appAdminCache',
        ];
        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                @array_map('unlink', (array) glob($dir . '/*.php') ?: []);
            }
        }
    } catch (\Throwable $e) {
        \PrestaShopLogger::addLog('[everpsblog][upgrade-6.0.8] ' . $e->getMessage(), 2);
    }

    return true;
}
