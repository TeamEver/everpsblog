<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_2()
{
    $result = true;
    $result &= Configuration::updateValue('EVERBLOG_SHOW_FEAT_POST', true);
    return (bool) $result;
}

