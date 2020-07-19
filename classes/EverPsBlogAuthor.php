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

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCleaner.php';

class EverPsBlogAuthor extends ObjectModel
{
    public $id_ever_author;
    public $meta_title;
    public $meta_description;
    public $link_rewrite;
    public $nickhandle;
    public $twitter;
    public $facebook;
    public $linkedin;
    public $content;
    public $id_lang;
    public $id_shop;
    public $id_author;
    public $date_add;
    public $date_upd;
    public $index;
    public $follow;
    public $active;

    public static $definition = array(
        'table' => 'ever_blog_author',
        'primary' => 'id_ever_author',
        'multilang' => true,
        'fields' => array(
            'nickhandle' => array(
                'type' => self::TYPE_HTML,
                'lang' => false,
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
                'validate' => 'isCleanHtml'
            ),
            'link_rewrite' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite'
            ),
            'twitter' => array(
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml'
            ),
            'facebook' => array(
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml'
            ),
            'linkedin' => array(
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml'
            ),
            'content' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'lang' => false,
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
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
        )
    );

    public static function getAllAuthors($id_lang, $id_shop, $active = 1)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_author', 'eba');
        $sql->leftJoin(
            'ever_blog_author_lang',
            'ebl',
            'ebl.id_ever_author = eba.id_ever_author'
        );
        $sql->where('eba.active = '.(int)$active);
        $sql->where('eba.id_shop = '.(int)$id_shop);
        $sql->where('ebl.id_lang = '.(int)$id_lang);
        $sql->orderBy('eba.date_add DESC');
        $authors = Db::getInstance()->executeS($sql);
        if (count($authors)) {
            return $authors;
        }
    }
}
