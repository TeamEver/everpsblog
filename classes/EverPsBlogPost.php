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
        );
        $shortcodes = array_merge($entityShortcodes, $defaultShortcodes);
        foreach ($shortcodes as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return $message;
    }

    public function convertProductToPsArray($product, $id_lang, $id_shop)
    {
        $protocol = Tools::getCurrentUrlProtocolPrefix();
        $link = new Link();
        $url = $link->getProductLink(
            (int)$product->id,
            null,
            null,
            null,
            (int)$id_lang,
            (int)$id_shop
        );
        // Product image
        $cover = Product::getCover(
            (int)$product->id
        );
        $cover_obj = new Image(
            (int)$cover['id_image'],
            (int)$id_lang
        );
        $image_small = ImageType::getFormattedName('small');
        $image_cart = ImageType::getFormattedName('cart');
        $image_medium = ImageType::getFormattedName('medium');
        $image_large = ImageType::getFormattedName('large');
        $image_home = ImageType::getFormattedName('home');
        $all_images = Image::getImages(
            (int)$id_lang,
            (int)$product->id
        );
        foreach ($all_images as $product_image) {
            $cover_small = $link->getImageLink(
                $product->link_rewrite,
                (int)$product->id.'-'.(int)$product_image['id_image'],
                $image_small
            );
            $cover_cart = $link->getImageLink(
                $product->link_rewrite,
                (int)$product->id.'-'.(int)$product_image['id_image'],
                $image_cart
            );
            $cover_medium = $link->getImageLink(
                $product->link_rewrite,
                (int)$product->id.'-'.(int)$product_image['id_image'],
                $image_medium
            );
            $cover_large = $link->getImageLink(
                $product->link_rewrite,
                (int)$product->id.'-'.(int)$product_image['id_image'],
                $image_large
            );
            $cover_home = $link->getImageLink(
                $product->link_rewrite,
                (int)$product->id.'-'.(int)$product_image['id_image'],
                $image_home
            );
            $cover_obj->bySize = array(
                ImageType::getFormattedName('small') => array(
                    'url' => $protocol.$cover_small
                ),
                ImageType::getFormattedName('cart') => array(
                    'url' => $protocol.$cover_cart
                ),
                ImageType::getFormattedName('medium') => array(
                    'url' => $protocol.$cover_medium
                ),
                ImageType::getFormattedName('large') => array(
                    'url' => $protocol.$cover_large
                ),
                ImageType::getFormattedName('home') => array(
                    'url' => $protocol.$cover_home
                )
            );
        }
        $cover_obj->large = array(
            'url' => $cover_home
        );
        // Has discount
        $product->has_discount = Product::isDiscounted(
            (int)$product->id
        );
        // Product default category
        $category = new Category(
            (int)$product->id_category_default,
            (int)$id_lang,
            (int)$id_shop
        );
        // Manufacturer
        $manufacturer = new Manufacturer(
            (int)$product->id_manufacturer,
            (int)$id_lang
        );
        // die(var_dump($cover_obj->bySize));
        $array_product = array(
            'id_product' => $product->id,
            'id_supplier' => $product->id_supplier,
            'id_manufacturer' => $product->id_manufacturer,
            'id_category_default' => $product->id_category_default,
            'id_shop_default' => $product->id_shop_default,
            'id_tax_rules_group' => $product->id_tax_rules_group,
            'on_sale' => $product->on_sale,
            'online_only' => $product->online_only,
            'ean13' => $product->ean13,
            'isbn' => $product->isbn,
            'upc' => $product->upc,
            'ecotax' => $product->ecotax,
            'quantity' => $product->quantity,
            'minimal_quantity' => $product->minimal_quantity,
            'low_stock_threshold' => $product->low_stock_threshold,
            'low_stock_alert' => $product->low_stock_alert,
            'wholesale_price' => $product->wholesale_price,
            'unity' => $product->unity,
            'unit_price_ratio' => $product->unit_price_ratio,
            'additional_shipping_cost' => $product->additional_shipping_cost,
            'reference' => $product->reference,
            'supplier_reference' => $product->supplier_reference,
            'location' => $product->location,
            'width' => $product->width,
            'height' => $product->height,
            'depth' => $product->depth,
            'weight' => $product->weight,
            'out_of_stock' => $product->out_of_stock,
            'additional_delivery_times' => $product->additional_delivery_times,
            'quantity_discount' => $product->quantity_discount,
            'customizable' => $product->customizable,
            'uploadable_files' => $product->uploadable_files,
            'text_fields' => $product->text_fields,
            'active' => $product->active,
            'redirect_type' => $product->redirect_type,
            'id_type_redirected' => $product->id_type_redirected,
            'available_for_order' => $product->available_for_order,
            'available_date' => $product->available_date,
            'show_condition' => $product->show_condition,
            'condition' => $product->condition,
            'show_price' => $product->show_price,
            'indexed' => $product->indexed,
            'visibility' => $product->visibility,
            'cache_is_pack' => $product->cache_is_pack,
            'cache_has_attachments' => $product->cache_has_attachments,
            'is_virtual' => $product->is_virtual,
            'cache_default_attribute' => $product->cache_default_attribute,
            'date_add' => $product->date_add,
            'advanced_stock_management' => $product->advanced_stock_management,
            'pack_stock_type' => $product->pack_stock_type,
            'state' => $product->state,
            'id_shop' => $product->id_shop,
            'id_product_attribute' => 0, //Set attribute to zero
            'product_attribute_minimal_quantity' => 0, //Set attribute minimal qty to zero
            'description' => $product->description,
            'description_short' => $product->description_short,
            'available_now' => $product->available_now,
            'available_later' => $product->available_later,
            'link_rewrite' => $product->link_rewrite,
            'meta_description' => $product->meta_description,
            'meta_keywords' => $product->meta_keywords,
            'meta_title' => $product->meta_title,
            'name' => $product->name,
            'id_image' => $cover_obj->id, // TODO get cover
            'legend' => $cover_obj->legend, // TODO cover legend
            'manufacturer_name' => $manufacturer->name, // TODO cover manufacturer_name
            'category_default' => $category, // TODO get category_default
            'new' => $product->new, // TODO is new ?
            'cover' => (array)$cover_obj, // TODO is new ?
            'url' => $url, // TODO is new ?
            'has_discount' => $product->has_discount,
            'flags' => array(), // TODO wut ? where are flags stored ?
            'main_variants' => false, // TODO wut ? where are main_variants stored ?
            // 'orderprice' => $product->orderprice, // wut ? orderprice ?
        );
        return $array_product;
    }
}
