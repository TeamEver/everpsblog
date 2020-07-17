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

function upgrade_module_2_3_8()
{
    Configuration::updateValue('EVERPSBLOG_EXCERPT', '150');
    Configuration::updateValue('EVERPSBLOG_TITLE_LENGTH', '15');
}
