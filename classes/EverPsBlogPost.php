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
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\AbstractLazyArray;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Core\Product\ProductInterface;

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCleaner.php';

class EverPsBlogPost extends ObjectModel
{
    public $id_ever_post;
    public $meta_title;
    public $meta_description;
    public $link_rewrite;
    public $title;
    public $content;
    public $id_lang;
    public $id_shop;
    public $date_add;
    public $date_upd;
    public $post_status;
    public $post_categories;
    public $post_tags;
    public $post_products;
    public $index;
    public $follow;
    public $limit;

    public static $definition = array(
        'table' => 'ever_blog_post',
        'primary' => 'id_ever_post',
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
                'validate' => 'isCleanHtml'
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
            'post_status' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isName',
                'required' => true
            ),
            'post_categories' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'post_tags' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
            'post_products' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => false
            ),
        )
    );

    public static function getPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
        if (!(int)$limit) {
            $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
        }
        $current_context = Context::getContext();
        if ($current_context->controller->controller_type == 'front') {
            # code...
        }
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->orderBy('bp.date_add DESC');
        $sql->limit((int)$limit, (int)$start);
        $posts = Db::getInstance()->executeS($sql);
        $return = array();
        // die(var_dump($current_context->controller->controller_type));
        if ($current_context->controller->controller_type == 'front'
            || $current_context->controller->controller_type == 'modulefront'
        ) {
            foreach ($posts as $post) {
                $post['content'] = self::changeShortcodes(
                    $post['content'],
                    (int)Context::getContext()->customer->id
                );
                $post['title'] = self::changeShortcodes(
                    $post['title'],
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post['title'] = Tools::substr(
                    $post['title'],
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post['content'] = Tools::substr(
                    $post['content'],
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $return[] = $post;
            }
        } else {
            $return = $posts;
        }
        return $return;
    }

    public static function getLatestPosts($id_lang, $id_shop, $start = 0, $limit = null, $post_status = 'published')
    {
        if (!(int)$limit) {
            $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
        }
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.pSQL($post_status).'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->limit((int)$limit, (int)$start);
        $sql->orderBy('bp.date_add DESC');
        $sql->orderBy('bp.id_ever_post DESC');
        $posts = Db::getInstance()->executeS($sql);
        return $posts;
    }

    public static function getPostsByTag($id_lang, $id_shop, $id_tag, $start = 0, $limit = null, $post_status = 'published')
    {
        if (!(int)$limit) {
            $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
        }
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->orderBy('bp.date_add DESC');
        $sql->limit((int)$limit, (int)$start);
        $posts = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($posts as $post) {
            $post_tags = EverPsBlogCleaner::convertToArray(
                json_decode($post['post_tags'])
            );
            if (in_array($id_tag, $post_tags)) {
                $post = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int)Context::getContext()->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    $post->content,
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $return[] = $post;
            }
        }
        if ($return) {
            return $return;
        }
    }

    public static function getPostsByCategory($id_lang, $id_shop, $id_category, $start = 0, $limit = null, $post_status = 'published')
    {
        if (!(int)$limit) {
            $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
        }
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.pSQL($post_status).'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->orderBy('bp.date_add DESC');
        $sql->limit((int)$limit, (int)$start);
        $posts = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($posts as $post) {
            $post_categories = EverPsBlogCleaner::convertToArray(
                json_decode($post['post_categories'])
            );
            if (in_array($id_category, $post_categories)) {
                $post = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int)Context::getContext()->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    $post->content,
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $return[] = $post;
            }
        }
        if ($return) {
            return $return;
        }
    }

    public static function getPostsByProduct($id_lang, $id_shop, $id_product, $start = 0, $limit = null, $post_status = 'published')
    {
        if (!(int)$limit) {
            $limit = (int)Configuration::get('EVERPSBLOG_PAGINATION');
        }
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $sql->orderBy('bp.date_add DESC');
        $sql->orderBy('bp.id_ever_post DESC');
        $sql->limit((int)$limit, (int)$start);
        $posts = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($posts as $post) {
            $post_products = EverPsBlogCleaner::convertToArray(
                json_decode($post['post_products'])
            );
            if (in_array((int)$id_product, $post_products)) {
                $post = new self(
                    (int)$post['id_ever_post'],
                    (int)$id_lang,
                    (int)$id_shop
                );
                $post->title = self::changeShortcodes(
                    $post->title,
                    (int)Context::getContext()->customer->id
                );
                $post->content = self::changeShortcodes(
                    $post->content,
                    (int)Context::getContext()->customer->id
                );
                // Length
                $post->title = Tools::substr(
                    $post->title,
                    0,
                    (int)Configuration::get('EVERPSBLOG_TITLE_LENGTH')
                );
                $post->content = Tools::substr(
                    $post->content,
                    0,
                    (int)Configuration::get('EVERPSBLOG_EXCERPT')
                );
                $return[] = $post;
            }
        }
        if ($return) {
            return $return;
        }
    }

    public static function getPostCategories($id_ever_post, $id_shop, $id_lang, $active = true)
    {
        $sql = new DbQuery();
        $sql->select('post_categories');
        $sql->from('ever_blog_post', 'ep');
        $sql->leftJoin(
            'ever_blog_post_lang',
            'bpl',
            'ep.id_ever_post = bpl.id_ever_post'
        );
        $sql->where(
            'ep.id_ever_post = '.(int)$id_ever_post
        );
        $sql->where(
            'ep.id_shop = '.(int)$id_shop
        );
        $sql->where(
            'ep.id_lang = '.(int)$id_lang
        );
        $sql->where(
            'ep.active = '.(int)$active
        );
        $sql->orderBy('ep.date_add DESC');
        // $sql->limit((int)$limit);
        $post_categories = Db::getInstance()->getValue($sql);
        return json_decode($post_categories);
    }

    public static function countPosts($id_lang, $id_shop, $post_status = 'published')
    {
        $sql = new DbQuery;
        $sql->select('COUNT(*)');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $count = Db::getInstance()->getValue($sql);
        if ($count) {
            return (int)$count;
        }
    }

    public static function countPostsByTag($id_tag, $id_lang, $id_shop, $post_status = 'published')
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $posts = Db::getInstance()->executeS($sql);
        $count = 0;
        foreach ($posts as $post) {
            $post_tags = EverPsBlogCleaner::convertToArray(
                json_decode($post['post_tags'])
            );
            if (in_array($id_tag, $post_tags)) {
                $count += 1;
            }
        }
        if ($count) {
            return $count;
        }
    }

    public static function countPostsByCategory($id_category, $id_lang, $id_shop, $post_status = 'published')
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('ever_blog_post_lang', 'bpl');
        $sql->leftJoin(
            'ever_blog_post',
            'bp',
            'bp.id_ever_post = bpl.id_ever_post'
        );
        $sql->where('bp.post_status = "'.(string)$post_status.'"');
        $sql->where('bp.id_shop = '.(int)$id_shop);
        $sql->where('bpl.id_lang = '.(int)$id_lang);
        $posts = Db::getInstance()->executeS($sql);
        $count = 0;
        foreach ($posts as $post) {
            $post_categories = EverPsBlogCleaner::convertToArray(
                json_decode($post['post_categories'])
            );
            if (in_array($id_category, $post_categories)) {
                $count += 1;
            }
        }
        if ($count) {
            return $count;
        }
    }

    public static function changeShortcodes($message, $id_entity = false)
    {
        $link = new Link();
        $contactLink = $link->getPageLink('contact');
        if ($id_entity && $id_entity > 0) {
            $entity = new Customer(
                (int)$id_entity
            );
            $gender = new Gender(
                (int)$entity->id_gender,
                (int)$entity->id_lang
            );
            $entityShortcodes = array(
                '[entity_lastname]' => $entity->lastname,
                '[entity_firstname]' => $entity->firstname,
                '[entity_company]' => $entity->company,
                '[entity_siret]' => $entity->siret,
                '[entity_ape]' => $entity->ape,
                '[entity_birthday]' => $entity->birthday,
                '[entity_website]' => $entity->website,
                '[entity_gender]' => $gender->name,
            );
        } else {
            $entityShortcodes = array(
                '[entity_lastname]' => '',
                '[entity_firstname]' => '',
                '[entity_company]' => '',
                '[entity_siret]' => '',
                '[entity_ape]' => '',
                '[entity_birthday]' => '',
                '[entity_website]' => '',
                '[entity_gender]' => '',
            );
        }
        $defaultShortcodes = array(
            '[shop_url]' => Tools::getShopDomainSsl(true),
            '[shop_name]'=> (string)Configuration::get('PS_SHOP_NAME'),
            '[start_cart_link]' => '<a href="'
            .Tools::getShopDomainSsl(true)
            .'/index.php?controller=cart&action=show" rel="nofollow" target="_blank">',
            '[end_cart_link]' => '</a>',
            '[start_shop_link]' => '<a href="'
            .Tools::getShopDomainSsl(true)
            .'" target="_blank">',
            '[start_contact_link]' => '<a href="'.$contactLink.'" rel="nofollow" target="_blank">',
            '[end_shop_link]' => '</a>',
            '[end_contact_link]' => '</a>',
            '1F600' => '😀',
            '1F601' => '😁',
            '1F602' => '😂',
            '1F603' => '😃',
            '1F604' => '😄',
            '1F605' => '😅',
            '1F606' => '😆',
            '1F607' => '😇',
            '1F608' => '😈',
            '1F609' => '😉',
            '1F60A' => '😊',
            '1F60B' => '😋',
            '1F60C' => '😌',
            '1F60D' => '😍',
            '1F60E' => '😎',
            '1F60F' => '😏',
            '1F610' => '😐',
            '1F611' => '😑',
            '1F612' => '😒',
            '1F613' => '😓',
            '1F614' => '😔',
            '1F615' => '😕',
            '1F616' => '😖',
            '1F617' => '😗',
            '1F618' => '😘',
            '1F619' => '😙',
            '1F61A' => '😚',
            '1F61B' => '😛',
            '1F61C' => '😜',
            '1F61D' => '😝',
            '1F61E' => '😞',
            '1F61F' => '😟',
            '1F620' => '😠',
            '1F621' => '😡',
            '1F622' => '😢',
            '1F623' => '😣',
            '1F624' => '😤',
            '1F625' => '😥',
            '1F626' => '😦',
            '1F627' => '😧',
            '1F628' => '😨',
            '1F629' => '😩',
            '1F62A' => '😪',
            '1F62B' => '😫',
            '1F62C' => '😬',
            '1F62D' => '😭',
            '1F62E' => '😮',
            '1F62F' => '😯',
            '1F630' => '😰',
            '1F631' => '😱',
            '1F632' => '😲',
            '1F633' => '😳',
            '1F634' => '😴',
            '1F635' => '😵',
            '1F636' => '😶',
            '1F637' => '😷',
            '1F641' => '🙁',
            '1F642' => '🙂',
            '1F643' => '🙃',
            '1F644' => '🙄',
            '1F910' => '🤐',
            '1F911' => '🤑',
            '1F912' => '🤒',
            '1F913' => '🤓',
            '1F914' => '🤔',
            '1F915' => '🤕',
            '1F920' => '🤠',
            '1F921' => '🤡',
            '1F922' => '🤢',
            '1F923' => '🤣',
            '1F924' => '🤤',
            '1F925' => '🤥',
            '1F927' => '🤧',
            '1F928' => '🤨',
            '1F929' => '🤩',
            '1F92A' => '🤪',
            '1F92B' => '🤫',
            '1F92C' => '🤬',
            '1F92D' => '🤭',
            '1F92E' => '🤮',
            '1F92F' => '🤯',
            '1F9D0' => '🧐',
        );
        $shortcodes = array_merge($entityShortcodes, $defaultShortcodes);
        foreach ($shortcodes as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }
}
