<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_7()
{
    if (!Configuration::get('EVERBLOG_HEADER_BG_COLOR')) {
        Configuration::updateValue('EVERBLOG_HEADER_BG_COLOR', '#0a0f54');
    }

    return true;
}
