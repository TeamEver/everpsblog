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
    public $bottom_content;
    public $id_ever_post_tag;
    public $id_lang;
    public $id_shop;
    public $active;
    public $date_add;
    public $date_upd;
    public $index;
    public $follow;
    public $sitemap;
    public $tag_products;
    public $count;

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
            'bottom_content' => array(
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
            'sitemap' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false
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
            'count' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt',
                'required' => false
            ),
        )
    );

    /**
     * Get all available tags
     * @param int id_lang, int id_shop, bool active (defaut 1)
     * @return array of all available tags
    */
    public static function getAllTags($id_lang, $id_shop, $active = 1)
    {
        $cache_id = 'EverPsBlogTag::getAllTags_'
        .(int) $id_lang
        .'_'
        .(int) $id_shop
        .'_'
        .(int) $active;
        if (!Cache::isStored($cache_id)) {
            $return = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'ever_blog_tag_lang` btl
                INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_tag` bt
                ON bt.id_ever_tag = btl.id_ever_tag
                WHERE bt.active = "'.(bool) $active.'"
                AND bt.id_shop = '.(int) $id_shop.'
                AND btl.id_lang = '.(int) $id_lang.''
            );
            Cache::store($cache_id, $return);
            return $return;
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get tag by link_rewrite
     * @param string tag link_rewrite
     * @return tag obj | false if not found
    */
    public static function getTagByLinkRewrite($link_rewrite)
    {
        $sql = new DbQuery;
        $sql->select('id_ever_tag');
        $sql->from('ever_blog_tag_lang');
        $sql->where('link_rewrite = "'.pSQL($link_rewrite).'"');
        $id_tag = Db::getInstance()->getValue($sql);
        if ($id_tag) {
            $return = new self($id_tag);
            return $return;
        }
        return false;
    }
}
