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

class EverPsBlogTag extends ObjectModel
{
    public $meta_title;
    public $meta_description;
    public $link_rewrite;
    public $title;
    public $content;
    public $id_ever_post_tag;
    public $id_lang;
    public $id_shop;
    public $active;
    public $date_add;
    public $date_upd;
    public $index;
    public $follow;
    public $tag_products;

    public static $definition = array(
        'table' => 'ever_blog_tag',
        'primary' => 'id_ever_tag',
        'multilang' => true,
        'fields' => array(
            'title' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'meta_title' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'meta_description' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' =>
                'isCleanHtml'
            ),
            'link_rewrite' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString'
            ),
            'content' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
            'index' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
            'follow' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
            'tag_products' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
        )
    );

    public static function getAllTags($id_lang, $id_shop, $active = 1)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'ever_blog_tag_lang` btl
            INNER JOIN `'._DB_PREFIX_.'ever_blog_tag` bt
            ON bt.id_ever_tag = btl.id_ever_tag
            WHERE bt.active = "'.(bool)$active.'"
            AND bt.id_shop = '.(int)$id_shop.'
            AND btl.id_lang = '.(int)$id_lang.''
        );
    }
}
