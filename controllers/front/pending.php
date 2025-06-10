<?php
/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsBlogpendingModuleFrontController extends ModuleFrontController
{
    public $controller_name = 'pending';

    public function init()
    {
        $this->smileys = [
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
            '🧐',
        ];
        $this->randSmiley = array_rand($this->smileys);
        if (!Tools::getValue('token')
            || Tools::encrypt('everpsblog/cron') != Tools::getValue('token')
            || !Module::isInstalled('everpsblog')
        ) {
            Tools::redirect('index.php');
        }
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::init();
    }

    public function initContent()
    {
        if (!Tools::getValue('token')
            || Tools::encrypt('everpsblog/cron') != Tools::getValue('token')
            || !Module::isInstalled('everpsblog')
        ) {
            Tools::redirect('index.php');
        }
        $everpsblog = Module::getInstanceByName('everpsblog');

        if (!$everpsblog->active) {
            Tools::redirect('index.php');
        }
        /* Check if the requested shop exists */
        $shops = Db::getInstance()->ExecuteS('SELECT id_shop FROM `' . _DB_PREFIX_ . 'shop`');
        $list_id_shop = [];
        foreach ($shops as $shop) {
            $list_id_shop[] = (int) $shop['id_shop'];
        }

        $id_shop = (Tools::getIsset('id_shop') && in_array(Tools::getValue('id_shop'), $list_id_shop))
            ? (int) Tools::getValue('id_shop') : (int)Configuration::get('PS_SHOP_DEFAULT');

        $everpsblog->cron = true;
        if ($everpsblog->sendPendingNotification((int) $id_shop)) {
            die(
                $this->smileys[$this->randSmiley]
                . ' All emails for pending posts have been sent '
                . $this->smileys[$this->randSmiley]
            );
        }
        Tools::redirect('index.php');
    }
}
