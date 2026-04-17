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
    public $bottom_content;
    public $id_lang;
    public $id_shop;
    public $id_author;
    public $date_add;
    public $date_upd;
    public $indexable;
    public $follow;
    public $sitemap;
    public $allowed_groups;
    public $author_products;
    public $active;
    public $count;

    public static $definition = [
        'table' => 'ever_blog_author',
        'primary' => 'id_ever_author',
        'multilang' => true,
        'multishop' => true,
        'fields' => [
            'nickhandle' => [
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml',
            ],
            'meta_title' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'meta_description' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'link_rewrite' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite',
            ],
            'twitter' => [
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml',
            ],
            'facebook' => [
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml',
            ],
            'linkedin' => [
                'type' => self::TYPE_HTML,
                'lang' => false,
                'validate' => 'isCleanHtml',
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'bottom_content' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'lang' => false,
                'validate' => 'isDate',
                'required' => false,
            ],
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'indexable' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'follow' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'sitemap' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'allowed_groups' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false,
            ],
            'author_products' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'count' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
        ],
    ];

    /**
     * Get all available authors
     * @param int id_lang, int id_shop, bool active (defaut 1)
     * @return array of all available authors
    */
    public static function getAllAuthors($id_lang, $id_shop, $active = 1)
    {
        $cache_id = 'EverPsBlogAuthor::getAllAuthors_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $active;
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from(self::$definition['table'], 'eba');
            $sql->leftJoin(
                self::$definition['table'] . '_shop',
                'ebas',
                'eba.' . self::$definition['primary'] . ' = ebas.' . self::$definition['primary']
                . ' AND ebas.id_shop = ' . (int) $id_shop
            );
            $sql->leftJoin(
                self::$definition['table'] . '_lang',
                'ebl',
                'ebl.' . self::$definition['primary'] . ' = eba.' . self::$definition['primary']
            );
            $sql->where('eba.active = ' . (int) $active);
            $sql->where('ebl.id_lang = ' . (int) $id_lang);
            $sql->orderBy('eba.date_add DESC');
            $authors = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            if (count($authors)) {
                Cache::store($cache_id, $authors);
                return $authors;
            }
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get author by nickhandle
     * @param string author nickhandle/name
     * @return author obj | false if not found
    */
    public static function getAuthorByNickhandle($nickhandle)
    {
        $sql = new DbQuery;
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        $sql->where('nickhandle = "' . pSQL($nickhandle) . '"');
        $id_author = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($id_author) {
            $return = new self($id_author);
            return $return;
        } else {
            return false;
        }
    }
}
