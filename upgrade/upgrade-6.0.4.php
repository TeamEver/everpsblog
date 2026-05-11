<?php

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_4()
{
    set_time_limit(0);
    $result = true;
    $db = Db::getInstance();

    $result &= (bool) everpsblogAddIndexIfMissing('ever_blog_post', 'idx_ever_blog_post_shop_status', '`id_shop`, `post_status`, `active`, `date_add`');
    $result &= (bool) everpsblogAddIndexIfMissing('ever_blog_post', 'idx_ever_blog_post_author', '`id_author`');
    $result &= (bool) everpsblogAddIndexIfMissing('ever_blog_post', 'idx_ever_blog_post_default_category', '`id_default_category`');
    $result &= (bool) everpsblogAddIndexIfMissing('ever_blog_comments', 'idx_ever_blog_comment_post_lang', '`id_ever_post`, `id_lang`, `active`, `date_add`');
    $result &= (bool) everpsblogAddIndexIfMissing('ever_blog_post_category', 'idx_ever_blog_post_category_reverse', '`id_ever_post_category`, `id_ever_post`');
    $result &= (bool) everpsblogAddIndexIfMissing('ever_blog_post_tag', 'idx_ever_blog_post_tag_reverse', '`id_ever_post_tag`, `id_ever_post`');
    $result &= (bool) everpsblogAddIndexIfMissing('ever_blog_post_product', 'idx_ever_blog_post_product_reverse', '`id_ever_post_product`, `id_ever_post`');

    $posts = $db->executeS('SELECT id_ever_post, id_default_category, post_categories, post_tags, post_products FROM `' . _DB_PREFIX_ . 'ever_blog_post`');

    foreach ($posts as $post) {
        $idPost = (int) $post['id_ever_post'];

        $categories = json_decode((string) $post['post_categories'], true);
        if (!is_array($categories)) {
            $categories = [];
        }
        if ((int) $post['id_default_category'] > 0) {
            $categories[] = (int) $post['id_default_category'];
        }
        $categories = array_unique(array_filter(array_map('intval', $categories)));

        foreach ($categories as $idCategory) {
            $result &= (bool) $db->execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ever_blog_post_category` (`id_ever_post_category`, `id_ever_post`) VALUES (' . (int) $idCategory . ', ' . $idPost . ')');
        }

        $tags = json_decode((string) $post['post_tags'], true);
        if (!is_array($tags)) {
            $tags = [];
        }
        $tags = array_unique(array_filter(array_map('intval', $tags)));
        foreach ($tags as $idTag) {
            $result &= (bool) $db->execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ever_blog_post_tag` (`id_ever_post_tag`, `id_ever_post`) VALUES (' . (int) $idTag . ', ' . $idPost . ')');
        }

        $products = json_decode((string) $post['post_products'], true);
        if (!is_array($products)) {
            $products = [];
        }
        $products = array_unique(array_filter(array_map('intval', $products)));
        foreach ($products as $idProduct) {
            $result &= (bool) $db->execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ever_blog_post_product` (`id_ever_post_product`, `id_ever_post`) VALUES (' . (int) $idProduct . ', ' . $idPost . ')');
        }
    }

    // Application-level integrity constraints (PrestaShop-friendly, no hard FK).
    $result &= (bool) $db->execute('DELETE epc
        FROM `' . _DB_PREFIX_ . 'ever_blog_post_category` epc
        LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post` ep ON ep.id_ever_post = epc.id_ever_post
        LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category` ec ON ec.id_ever_category = epc.id_ever_post_category
        WHERE ep.id_ever_post IS NULL OR ec.id_ever_category IS NULL');

    $result &= (bool) $db->execute('DELETE ept
        FROM `' . _DB_PREFIX_ . 'ever_blog_post_tag` ept
        LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post` ep ON ep.id_ever_post = ept.id_ever_post
        LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_tag` et ON et.id_ever_tag = ept.id_ever_post_tag
        WHERE ep.id_ever_post IS NULL OR et.id_ever_tag IS NULL');

    $result &= (bool) $db->execute('DELETE epp
        FROM `' . _DB_PREFIX_ . 'ever_blog_post_product` epp
        LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post` ep ON ep.id_ever_post = epp.id_ever_post
        LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = epp.id_ever_post_product
        WHERE ep.id_ever_post IS NULL OR p.id_product IS NULL');

    // Progressive deprecation marker for redundant JSON columns.
    $result &= (bool) Configuration::updateValue('EVERBLOG_POST_JSON_DEPRECATED', 1);

    return (bool) $result;
}

function everpsblogAddIndexIfMissing($table, $indexName, $fields)
{
    $indexExists = (bool) Db::getInstance()->getValue(
        'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = "' . pSQL(_DB_PREFIX_ . $table) . '"
        AND INDEX_NAME = "' . pSQL($indexName) . '"'
    );

    if ($indexExists) {
        return true;
    }

    return Db::getInstance()->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '` ADD INDEX `' . bqSQL($indexName) . '` (' . $fields . ')'
    );
}
