<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_6()
{
    $db = Db::getInstance();
    $result = true;

    $columnExists = $db->executeS(
        'DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_author_lang` `excerpt`'
    );

    if (!$columnExists) {
        $result &= (bool) $db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_author_lang`
             ADD `excerpt` varchar(255) DEFAULT NULL
             AFTER `content`'
        );
    }

    return (bool) $result;
}
