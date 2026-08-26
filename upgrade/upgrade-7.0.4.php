<?php

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Le bloc "derniers articles" de la home est desormais rendu par le bloc
 * QCD Page Builder everpsblog_latest_posts, place ou l'integrateur le souhaite
 * dans la page. Le hook displayHome faisait donc doublon et injectait un <h2>
 * avant le <h1> de la page, ce qui cassait la hierarchie des titres.
 */
function upgrade_module_7_0_4()
{
    $module = \Module::getInstanceByName('everpsblog');
    if (!\Validate::isLoadedObject($module)) {
        return false;
    }

    if ($module->isRegisteredInHook('displayHome')) {
        $module->unregisterHook((int) \Hook::getIdByName('displayHome'));
    }

    if (\Hook::getIdByName('filterQcdPageBuilderDeclarativeBlocks')) {
        $module->registerHook('filterQcdPageBuilderDeclarativeBlocks');
    }

    if (\Hook::getIdByName('filterQcdPageBuilderThirdPartyBlockFrontRender')) {
        $module->registerHook('filterQcdPageBuilderThirdPartyBlockFrontRender');
    }

    \Configuration::deleteByName('EVERPSBLOG_QCDPB_HOOKS');

    try {
        if (method_exists('\\Tools', 'clearCache')) {
            \Tools::clearCache();
        }
        if (method_exists('\\Tools', 'clearSmartyCache')) {
            \Tools::clearSmartyCache();
        }
    } catch (\Throwable $exception) {
        // Le vidage de cache n'est pas bloquant pour la mise a jour.
    }

    return true;
}
