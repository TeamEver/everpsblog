<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_0()
{
    set_time_limit(0);
    $result = true;
    $db = Db::getInstance();

    $associations = [
        'ever_blog_post' => [
            'table' => 'ever_blog_post_shop',
            'primary' => 'id_ever_post',
        ],
        'ever_blog_category' => [
            'table' => 'ever_blog_category_shop',
            'primary' => 'id_ever_category',
        ],
        'ever_blog_tag' => [
            'table' => 'ever_blog_tag_shop',
            'primary' => 'id_ever_tag',
        ],
        'ever_blog_author' => [
            'table' => 'ever_blog_author_shop',
            'primary' => 'id_ever_author',
        ],
        'ever_blog_image' => [
            'table' => 'ever_blog_image_shop',
            'primary' => 'id_ever_image',
        ],
    ];

    foreach ($associations as $source => $data) {
        $shopTable = _DB_PREFIX_.$data['table'];
        $primary = $data['primary'];

        $create = 'CREATE TABLE IF NOT EXISTS `'.$shopTable.'` (
            `'.$primary.'` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
            PRIMARY KEY (`'.$primary.'`, `id_shop`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
        $result &= $db->execute($create);

        $insert = 'INSERT IGNORE INTO `'.$shopTable.'` (`'.$primary.'`, `id_shop`)
            SELECT `'.$primary.'`, `id_shop` FROM `'._DB_PREFIX_.$source.'`';
        $result &= $db->execute($insert);
    }

    return (bool)$result;
}
