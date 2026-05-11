<?php

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_5()
{
    $result = true;

    $map = [
        'everpsblog_admin_post' => 'AdminEverPsBlogPost',
        'everpsblog_admin_category' => 'AdminEverPsBlogCategory',
        'everpsblog_admin_tag' => 'AdminEverPsBlogTag',
        'everpsblog_admin_comment' => 'AdminEverPsBlogComment',
        'everpsblog_admin_author' => 'AdminEverPsBlogAuthor',
    ];

    foreach ($map as $legacyClass => $modernClass) {
        $result &= (bool) Db::getInstance()->update(
            'tab',
            ['class_name' => pSQL($modernClass)],
            'class_name = "' . pSQL($legacyClass) . '"'
        );
    }

    return (bool) $result;
}
