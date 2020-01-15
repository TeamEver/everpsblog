<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @link https://www.team-ever.com
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

$smileys = array(
    '😀',
    '😁',
    '😃',
    '😄',
    '😇',
    '😉',
    '😊',
    '😋',
    '😌',
    '😎',
    '😏',
    '😗',
    '😘',
    '😙',
    '😚',
    '😛',
    '😜',
    '😝',
    '😬',
    '😶',
    '🙂',
    '🙃',
    '🙄',
    '🤐',
    '🤫',
    '🧐'
);
$randSmiley = array_rand($smileys);
if (!Tools::getIsset('token')
    || Tools::substr(Tools::encrypt('everpsblog/cron'), 0, 10) != Tools::getValue('token')
    || !Module::isInstalled('everpsblog')
) {
    die($smileys[$randSmiley].' Holy crap ! Invalid token... '.$smileys[$randSmiley]);
}

$everpsblog = Module::getInstanceByName('everpsblog');

if (!$everpsblog->active) {
    die($smileys[$randSmiley].' Sorry, the module EverPsBlog is not active '.$smileys[$randSmiley]);
}
/* Check if the requested shop exists */
$shops = Db::getInstance()->ExecuteS('SELECT id_shop FROM `'._DB_PREFIX_.'shop`');

$list_id_shop = array();
foreach ($shops as $shop) {
    $list_id_shop[] = (int)$shop['id_shop'];
}

$id_shop = (Tools::getIsset('id_shop') && in_array(Tools::getValue('id_shop'), $list_id_shop))
    ? (int)Tools::getValue('id_shop') : (int)Configuration::get('PS_SHOP_DEFAULT');

$everpsblog->cron = true;
if ($everpsblog->emptyTrash((int)$id_shop)) {
    die($smileys[$randSmiley].' Trash has been emptied '.$smileys[$randSmiley]);
}