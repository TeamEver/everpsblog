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

class EverPsBlogSortOrders
{
    public static function getSortOrders()
    {
        $module = Module::getInstanceByName('everpsblog');

        if (!$module) {
            return [];
        }

        $sortOrders = [
            [
                'label' => $module->l('Most viewed'),
                'order_by' => 'count',
                'order_way' => 'desc'
            ],
            [
                'label' => $module->l('Most recent'),
                'order_by' => 'date_add',
                'order_way' => 'desc'
            ],
            [
                'label' => $module->l('The oldest'),
                'order_by' => 'date_add',
                'order_way' => 'asc'
            ],
        ];

        foreach ($sortOrders as &$sortOrder) {
            $orderBy = $sortOrder['order_by'];
            $orderWay = $sortOrder['order_way'];
            
            $sortOrder['current'] = $orderBy === Tools::getValue('orderby') && $orderWay === Tools::getValue('orderway');
            $sortOrder['url'] = self::buildSortLink($orderBy, $orderWay);
        }

        return $sortOrders;
    }

    private function buildSortLink(string $orderBy, string $orderWay): string
    {
        // Récupération de l'URL courante
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        // Analyse de l'URL pour extraire ses composants
        $parsedUrl = parse_url($url);

        // Analyse de la chaîne de requête pour extraire les paramètres existants
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Ajout ou remplacement des paramètres
        $queryParams['orderby'] = $orderBy;
        $queryParams['orderway'] = $orderWay;

        // Reconstruction de la chaîne de requête
        $newQuery = http_build_query($queryParams);

        // Reconstruction de l'URL avec les nouveaux paramètres
        $newUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') . $parsedUrl['path'] . '?' . $newQuery . (isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '');

        // Affichage de l'URL mise à jour
        return $newUrl;
    }
}
