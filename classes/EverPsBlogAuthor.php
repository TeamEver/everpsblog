<?php
/**
 * 2019-2020 Team Ever
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
    public $sitemap;
    public $author_products;
    public $active;
    public $count;

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
            'sitemap' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
            ),
            'author_products' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
            'count' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
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
