<?php
/**
 * 2019-2021 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class EverPsBlogCleaner extends ObjectModel
{
    public $string;

    /**
     * Convert string to link rewrite
     *
     * @param $string
     * @return link rewrite
     */
    public static function convertToUrlRewrite($string)
    {
        $string = preg_replace(
            '/\\\[px]\{[a-z]{1,2}\}|(\/[a-z]*)u([a-z]*)$/i',
            '$1$2',
            $string
        );

        $string = preg_replace("/[^A-Za-z0-9 ]/", '', $string);

        $string = html_entity_decode(
            htmlspecialchars_decode(
                $string,
                ENT_HTML5
            )
        );

        $string = html_entity_decode(
            htmlspecialchars_decode(
                $string,
                ENT_QUOTES
            )
        );
        $string = str_replace(' ', '-', $string);
        $string = Tools::strtolower($string);
        return $string;
    }

    /**
     * Check if is valid array by forcing it to convert into array
     * @param supposed array
     * @return array
    */
    public static function convertToArray($array)
    {
        if (!is_array($array)) {
            $array = array($array);
        }
        foreach ($array as $key => $value) {
            if (Validate::isInt($value)) {
                $array[$key] = (int)$value;
            }
        }
        return $array;
    }
}
