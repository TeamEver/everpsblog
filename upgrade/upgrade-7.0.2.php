<?php

declare(strict_types=1);


if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_0_2()
{
    $db = Db::getInstance();
    $result = true;

    foreach ([
        'ever_blog_post_lang',
    ] as $table) {
        $columnExists = $db->executeS(
            'DESCRIBE `' . _DB_PREFIX_ . bqSQL($table) . '` `excerpt`'
        );

        if ($columnExists) {
            $result &= (bool) $db->execute(
                'ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '`
                 MODIFY `excerpt` text DEFAULT NULL'
            );
        }
    }

    return (bool) $result;
}
