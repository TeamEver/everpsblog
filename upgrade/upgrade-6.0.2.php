<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_2()
{
    return Configuration::updateValue('EVERBLOG_SHOW_SUBCATS', true);
}
