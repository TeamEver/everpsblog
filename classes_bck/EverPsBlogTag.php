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
    public $indexable;
    public $follow;
    public $sitemap;
    public $allowed_groups;
    public $tag_products;
    public $count;

    public static $definition = [
        'table' => 'ever_blog_tag',
        'primary' => 'id_ever_tag',
        'multilang' => true,
        'multishop' => true,
        'fields' => [
            'title' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
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
                'validate' => 'isString',
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
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
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
            'tag_products' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false,
            ],
            'allowed_groups' => [
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
     * Get all available tags
     * @param int id_lang, int id_shop, bool active (defaut 1)
     * @return array of all available tags
    */
    public static function getAllTags($id_lang, $id_shop, $active = 1)
    {
        $cache_id = 'EverPsBlogTag::getAllTags_'
        . (int) $id_lang
        . '_'
        . (int) $id_shop
        . '_'
        . (int) $active;
        if (!Cache::isStored($cache_id)) {
            $context = Context::getContext();
            $tags = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` btl
                INNER JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '` bt
                    ON bt.' . self::$definition['primary'] . ' = btl.' . self::$definition['primary'] . '
                INNER JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_shop` bts
                    ON bts.' . self::$definition['primary'] . ' = bt.' . self::$definition['primary'] . '
                    AND bts.id_shop = ' . (int) $id_shop . '
                WHERE bt.active = "' . (bool) $active . '"
                AND btl.id_lang = ' . (int) $id_lang
            );
            $return = [];
            foreach ($tags as $tag) {
                if ($context->controller->controller_type == 'front'
                    || $context->controller->controller_type == 'modulefront'
                ) {
                    if (isset($tag['allowed_groups'])
                        && $tag['allowed_groups']
                    ) {
                        $allowedGroups = json_decode($tag['allowed_groups']);
                        $customerGroups = Customer::getGroupsStatic(
                            (int) $context->customer->id
                        );
                        if (isset($customerGroups)
                            && !empty($allowedGroups)
                            && !array_intersect($allowedGroups, $customerGroups)
                        ) {
                            continue;
                        }
                    }
                }
                $return[] = $tag;
            }
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
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table'] . '_lang');
        $sql->where('link_rewrite = "' . pSQL($link_rewrite) . '"');
        $id_tag = Db::getInstance()->getValue($sql);
        if ($id_tag) {
            $return = new self($id_tag);
            return $return;
        }
        return false;
    }
}
