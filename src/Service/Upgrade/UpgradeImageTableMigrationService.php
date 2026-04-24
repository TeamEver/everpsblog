<?php

namespace PrestaShop\Module\Everpsblog\Service\Upgrade;

class UpgradeImageTableMigrationService
{
    public function migrate(): bool
    {
        $result = true;

        $db = \Db::getInstance();

        if (!$db->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ever_blog_image` (
                `id_ever_image` int(10) unsigned NOT NULL auto_increment,
                `image_type` varchar(255) DEFAULT NULL,
                `image_link` varchar(255) DEFAULT NULL,
                `id_element` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_ever_image`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
        )) {
            $result = false;
        }

        $shops = $db->executeS('SELECT id_shop FROM `' . _DB_PREFIX_ . 'shop`') ?: [];
        foreach ($shops as $shop) {
            $shopId = (int) $shop['id_shop'];
            $result = $this->migrateType('ever_blog_post', 'id_ever_post', 'post', 'posts/post_image_', $shopId) && $result;
            $result = $this->migrateType('ever_blog_category', 'id_ever_category', 'category', 'categories/category_image_', $shopId) && $result;
            $result = $this->migrateType('ever_blog_tag', 'id_ever_tag', 'tag', 'tags/tag_image_', $shopId) && $result;
            $result = $this->migrateType('ever_blog_author', 'id_ever_author', 'author', 'authors/author_image_', $shopId) && $result;
        }

        return $result;
    }

    private function migrateType(string $table, string $idColumn, string $imageType, string $prefix, int $shopId): bool
    {
        $db = \Db::getInstance();
        $rows = $db->executeS('SELECT `' . bqSQL($idColumn) . '` AS id_element FROM `' . _DB_PREFIX_ . bqSQL($table) . '`') ?: [];
        $result = true;

        foreach ($rows as $row) {
            $idElement = (int) $row['id_element'];
            if ($idElement <= 0) {
                continue;
            }

            $legacyPath = _PS_MODULE_DIR_ . 'everpsblog/views/img/' . $prefix . $idElement . '.jpg';
            if (!is_file($legacyPath)) {
                continue;
            }

            $exists = (int) $db->getValue(
                'SELECT id_ever_image
                FROM `' . _DB_PREFIX_ . 'ever_blog_image`
                WHERE id_element = ' . $idElement . '
                    AND id_shop = ' . $shopId . '
                    AND image_type = "' . pSQL($imageType) . '"'
            );

            if ($exists > 0) {
                continue;
            }

            $result = $db->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'ever_blog_image` (image_type, image_link, id_element, id_shop)
                VALUES (
                    "' . pSQL($imageType) . '",
                    "' . pSQL($prefix . $idElement . '.jpg') . '",
                    ' . $idElement . ',
                    ' . $shopId . '
                )'
            ) && $result;
        }

        return $result;
    }
}
