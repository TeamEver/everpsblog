<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

class EverPsBlogCleaner extends ObjectModel
{
    public $string;

    /**
     * Auto create Prestashop category
     *
     * @param $winparf_category name, $id_shop
     * @return url rewrite
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
        return $string;
    }

    public static function slugifyLink($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = Tools::strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function isInvalidCutomerName($domain_name)
    {
        $validityPattern = Tools::cleanNonUnicodeSupport(
            '/^(?:[^0-9!<>,;?=+()\/\\@#"°*`{}_^$%:¤\[\]|\.。]|[\.。](?:\s|$))*$/u'
        );

        return !preg_match($validityPattern, $domain_name);
    }

    public static function convertToArray($array)
    {
        if (!is_array($array)) {
            $array = array($array);
        }
        return $array;
    }
}
