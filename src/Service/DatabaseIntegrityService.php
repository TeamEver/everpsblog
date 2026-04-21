<?php

namespace PrestaShop\Module\Everpsblog\Service;

final class DatabaseIntegrityService
{
    public function checkAndFix(): bool
    {
        $result = $this->createMissingTables();

        foreach ($this->getExpectedColumns() as $table => $columns) {
            if (!$this->tableExists($table)) {
                \PrestaShopLogger::addLog('[everpsblog][DatabaseIntegrity] Missing table after create pass: ' . $table, 3);
                $result = false;
                continue;
            }

            foreach ($columns as $column => $definition) {
                if (!$this->columnExists($table, $column)) {
                    $result = $this->addColumn($table, $column, $definition) && $result;
                }
            }
        }

        foreach ($this->getExpectedIndexes() as $table => $indexes) {
            if (!$this->tableExists($table)) {
                continue;
            }

            foreach ($indexes as $indexName => $definition) {
                if (!$this->indexExists($table, $indexName)) {
                    $result = $this->addIndex($table, $definition) && $result;
                }
            }
        }

        return (bool) $result;
    }

    private function createMissingTables(): bool
    {
        $installFile = dirname(__DIR__, 2) . '/install/install.php';
        if (!is_file($installFile)) {
            \PrestaShopLogger::addLog('[everpsblog][DatabaseIntegrity] install/install.php not found.', 3);

            return false;
        }

        $result = include $installFile;

        return false !== $result;
    }

    private function addColumn(string $table, string $column, string $definition): bool
    {
        try {
            $columnDefinition = $definition;
            if (false !== stripos($definition, 'auto_increment') && !$this->indexExists($table, 'PRIMARY')) {
                $columnDefinition .= ' PRIMARY KEY';
            }

            return (bool) \Db::getInstance()->execute(
                'ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '`
                 ADD `' . bqSQL($column) . '` ' . $columnDefinition
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                sprintf('[everpsblog][DatabaseIntegrity] Unable to add %s.%s: %s', $table, $column, $exception->getMessage()),
                3
            );

            return false;
        }
    }

    private function addIndex(string $table, string $definition): bool
    {
        try {
            return (bool) \Db::getInstance()->execute(
                'ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '` ADD ' . $definition
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                sprintf('[everpsblog][DatabaseIntegrity] Unable to add index on %s: %s', $table, $exception->getMessage()),
                2
            );

            return false;
        }
    }

    private function tableExists(string $table): bool
    {
        return (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = "' . pSQL(_DB_PREFIX_ . $table) . '"'
        );
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = "' . pSQL(_DB_PREFIX_ . $table) . '"
             AND COLUMN_NAME = "' . pSQL($column) . '"'
        );
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*)
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = "' . pSQL(_DB_PREFIX_ . $table) . '"
             AND INDEX_NAME = "' . pSQL($indexName) . '"'
        );
    }

    private function getExpectedColumns(): array
    {
        return [
            'ever_blog_post' => [
                'id_ever_post' => 'int(10) unsigned NOT NULL auto_increment',
                'id_shop' => 'int(10) unsigned NOT NULL',
                'id_author' => 'int(10) unsigned NOT NULL',
                'id_default_category' => 'int(10) unsigned NOT NULL',
                'post_status' => 'varchar(255) NOT NULL',
                'date_add' => 'DATETIME DEFAULT NULL',
                'date_upd' => 'DATETIME DEFAULT NULL',
                'indexable' => 'int(1) unsigned DEFAULT NULL',
                'follow' => 'int(1) unsigned DEFAULT NULL',
                'sitemap' => 'int(1) unsigned DEFAULT 1',
                'active' => 'int(1) unsigned DEFAULT NULL',
                'allowed_groups' => 'varchar(255) DEFAULT NULL',
                'post_categories' => 'varchar(255) DEFAULT NULL',
                'post_tags' => 'varchar(255) DEFAULT NULL',
                'post_products' => 'varchar(255) DEFAULT NULL',
                'psswd' => 'varchar(255) DEFAULT NULL',
                'starred' => 'int(10) unsigned DEFAULT 0',
                'count' => 'int(10) unsigned DEFAULT 0',
                'groups' => 'text DEFAULT NULL',
            ],
            'ever_blog_post_lang' => [
                'id_ever_post' => 'int(10) unsigned NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'meta_title' => 'varchar(255) DEFAULT NULL',
                'meta_description' => 'varchar(255) DEFAULT NULL',
                'link_rewrite' => 'varchar(255) DEFAULT NULL',
                'content' => 'text NOT NULL',
                'excerpt' => 'varchar(255) DEFAULT NULL',
                'id_lang' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_post_shop' => [
                'id_ever_post' => 'int(10) unsigned NOT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_category' => [
                'id_ever_category' => 'int(10) unsigned NOT NULL auto_increment',
                'id_parent_category' => 'int(10) DEFAULT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
                'date_add' => 'DATETIME DEFAULT NULL',
                'date_upd' => 'DATETIME DEFAULT NULL',
                'indexable' => 'int(1) unsigned DEFAULT NULL',
                'follow' => 'int(1) unsigned DEFAULT NULL',
                'sitemap' => 'int(1) unsigned DEFAULT 1',
                'active' => 'int(1) unsigned DEFAULT NULL',
                'category_products' => 'varchar(255) DEFAULT NULL',
                'allowed_groups' => 'varchar(255) DEFAULT NULL',
                'is_root_category' => 'int(1) unsigned DEFAULT NULL',
                'count' => 'int(10) unsigned DEFAULT 0',
                'groups' => 'text DEFAULT NULL',
            ],
            'ever_blog_category_lang' => [
                'id_ever_category' => 'int(10) unsigned NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'meta_title' => 'varchar(255) DEFAULT NULL',
                'meta_description' => 'varchar(255) DEFAULT NULL',
                'link_rewrite' => 'varchar(255) DEFAULT NULL',
                'content' => 'text NOT NULL',
                'bottom_content' => 'text DEFAULT NULL',
                'id_lang' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_category_shop' => [
                'id_ever_category' => 'int(10) unsigned NOT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_tag' => [
                'id_ever_tag' => 'int(10) unsigned NOT NULL auto_increment',
                'id_shop' => 'int(10) unsigned NOT NULL',
                'date_add' => 'DATETIME DEFAULT NULL',
                'date_upd' => 'DATETIME DEFAULT NULL',
                'indexable' => 'int(10) unsigned DEFAULT NULL',
                'follow' => 'int(10) unsigned DEFAULT NULL',
                'sitemap' => 'int(1) unsigned DEFAULT 1',
                'active' => 'int(1) unsigned DEFAULT NULL',
                'allowed_groups' => 'varchar(255) DEFAULT NULL',
                'tag_products' => 'varchar(255) DEFAULT NULL',
                'count' => 'int(10) unsigned DEFAULT 0',
            ],
            'ever_blog_tag_lang' => [
                'id_ever_tag' => 'int(10) unsigned NOT NULL',
                'title' => 'varchar(255) NOT NULL',
                'meta_title' => 'varchar(255) DEFAULT NULL',
                'meta_description' => 'varchar(255) DEFAULT NULL',
                'link_rewrite' => 'varchar(255) DEFAULT NULL',
                'content' => 'text NOT NULL',
                'bottom_content' => 'text DEFAULT NULL',
                'id_lang' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_tag_shop' => [
                'id_ever_tag' => 'int(10) unsigned NOT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_comments' => [
                'id_ever_comment' => 'int(10) unsigned NOT NULL auto_increment',
                'id_ever_post' => 'int(10) unsigned NOT NULL',
                'id_lang' => 'int(10) unsigned NOT NULL',
                'comment' => 'text NOT NULL',
                'name' => 'text NOT NULL',
                'user_email' => 'text NOT NULL',
                'date_add' => 'DATETIME DEFAULT NULL',
                'date_upd' => 'DATETIME DEFAULT NULL',
                'active' => 'int(10) DEFAULT NULL',
            ],
            'ever_blog_author' => [
                'id_ever_author' => 'int(10) unsigned NOT NULL auto_increment',
                'id_employee' => 'int(10) unsigned NOT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
                'nickhandle' => 'varchar(255) NOT NULL',
                'twitter' => 'varchar(255) DEFAULT NULL',
                'facebook' => 'varchar(255) DEFAULT NULL',
                'linkedin' => 'varchar(255) DEFAULT NULL',
                'date_add' => 'DATETIME DEFAULT NULL',
                'date_upd' => 'DATETIME DEFAULT NULL',
                'indexable' => 'int(10) unsigned DEFAULT NULL',
                'follow' => 'int(10) unsigned DEFAULT NULL',
                'sitemap' => 'int(1) unsigned DEFAULT 1',
                'allowed_groups' => 'varchar(255) DEFAULT NULL',
                'author_products' => 'varchar(255) DEFAULT NULL',
                'active' => 'int(10) unsigned DEFAULT NULL',
                'count' => 'int(10) unsigned DEFAULT 0',
            ],
            'ever_blog_author_lang' => [
                'id_ever_author' => 'int(10) unsigned NOT NULL',
                'meta_title' => 'varchar(255) DEFAULT NULL',
                'meta_description' => 'varchar(255) DEFAULT NULL',
                'link_rewrite' => 'varchar(255) DEFAULT NULL',
                'content' => 'text NOT NULL',
                'excerpt' => 'varchar(255) DEFAULT NULL',
                'bottom_content' => 'text DEFAULT NULL',
                'id_lang' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_author_shop' => [
                'id_ever_author' => 'int(10) unsigned NOT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_image' => [
                'id_ever_image' => 'int(10) unsigned NOT NULL auto_increment',
                'image_type' => 'varchar(255) DEFAULT NULL',
                'image_link' => 'varchar(255) DEFAULT NULL',
                'id_element' => 'int(10) unsigned NOT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_image_shop' => [
                'id_ever_image' => 'int(10) unsigned NOT NULL',
                'id_shop' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_post_category' => [
                'id_ever_post_category' => 'int(10) NOT NULL',
                'id_ever_post' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_post_tag' => [
                'id_ever_post_tag' => 'int(10) NOT NULL',
                'id_ever_post' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_post_product' => [
                'id_ever_post_product' => 'int(10) NOT NULL',
                'id_ever_post' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_category_product' => [
                'id_ever_category_product' => 'int(10) NOT NULL',
                'id_ever_category' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_tag_product' => [
                'id_ever_tag_product' => 'int(10) NOT NULL',
                'id_ever_tag' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_author_product' => [
                'id_ever_author_product' => 'int(10) NOT NULL',
                'id_ever_author' => 'int(10) unsigned NOT NULL',
            ],
            'ever_blog_redirect' => [
                'id_ever_redirect' => 'int(10) unsigned NOT NULL auto_increment',
                'id_shop' => 'int(10) unsigned NOT NULL',
                'source_url' => 'varchar(2048) DEFAULT NULL',
                'source_path' => 'varchar(1024) NOT NULL',
                'source_hash' => 'char(32) NOT NULL',
                'target_url' => 'varchar(2048) NOT NULL',
                'entity_type' => 'varchar(32) DEFAULT NULL',
                'id_element' => 'int(10) unsigned DEFAULT NULL',
                'http_code' => 'smallint(3) unsigned DEFAULT 301',
                'active' => 'int(1) unsigned DEFAULT 1',
                'hits' => 'int(10) unsigned DEFAULT 0',
                'last_hit' => 'DATETIME DEFAULT NULL',
                'date_add' => 'DATETIME DEFAULT NULL',
                'date_upd' => 'DATETIME DEFAULT NULL',
            ],
        ];
    }

    private function getExpectedIndexes(): array
    {
        return [
            'ever_blog_post' => [
                'idx_ever_blog_post_shop_status' => 'KEY `idx_ever_blog_post_shop_status` (`id_shop`, `post_status`, `active`, `date_add`)',
                'idx_ever_blog_post_author' => 'KEY `idx_ever_blog_post_author` (`id_author`)',
                'idx_ever_blog_post_default_category' => 'KEY `idx_ever_blog_post_default_category` (`id_default_category`)',
            ],
            'ever_blog_comments' => [
                'idx_ever_blog_comment_post_lang' => 'KEY `idx_ever_blog_comment_post_lang` (`id_ever_post`, `id_lang`, `active`, `date_add`)',
            ],
            'ever_blog_post_category' => [
                'idx_ever_blog_post_category_reverse' => 'KEY `idx_ever_blog_post_category_reverse` (`id_ever_post_category`, `id_ever_post`)',
            ],
            'ever_blog_post_tag' => [
                'idx_ever_blog_post_tag_reverse' => 'KEY `idx_ever_blog_post_tag_reverse` (`id_ever_post_tag`, `id_ever_post`)',
            ],
            'ever_blog_post_product' => [
                'idx_ever_blog_post_product_reverse' => 'KEY `idx_ever_blog_post_product_reverse` (`id_ever_post_product`, `id_ever_post`)',
            ],
            'ever_blog_redirect' => [
                'uniq_ever_blog_redirect_shop_source' => 'UNIQUE KEY `uniq_ever_blog_redirect_shop_source` (`id_shop`, `source_hash`)',
                'idx_ever_blog_redirect_active' => 'KEY `idx_ever_blog_redirect_active` (`active`, `id_shop`)',
            ],
        ];
    }
}
