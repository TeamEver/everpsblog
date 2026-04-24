<?php

namespace PrestaShop\Module\Everpsblog\Service\Upgrade;

class Upgrade301MigrationService
{
    /**
     * @var array<string, string>
     */
    private $hookMap = [
        'beforeEverPostInitContent' => 'actionBeforeEverPostInitContent',
        'beforeEverCategoryInitContent' => 'actionBeforeEverCategoryInitContent',
        'beforeEverTagInitContent' => 'actionBeforeEverTagInitContent',
        'beforeEverBlogInitContent' => 'actionBeforeEverBlogInitContent',
        'beforeEverBlogInit' => 'actionBeforeEverBlogInit',
        'afterEverBlogInit' => 'actionAfterEverBlogInit',
    ];

    public function migrate(): bool
    {
        $result = true;

        if (!$this->renameHooks()) {
            $result = false;
        }
        if (!$this->ensureAuthorColumn()) {
            $result = false;
        }
        if (!$this->ensureTables()) {
            $result = false;
        }
        if (!$this->ensureAuthorTab()) {
            $result = false;
        }
        if (!$this->migratePostJsonRelations()) {
            $result = false;
        }

        return $result;
    }

    private function renameHooks(): bool
    {
        $db = \Db::getInstance();
        $result = true;

        foreach ($this->hookMap as $oldName => $newName) {
            $result = $db->execute(
                'UPDATE `' . _DB_PREFIX_ . 'hook`
                SET name = "' . pSQL($newName) . '"
                WHERE name = "' . pSQL($oldName) . '"
                  AND NOT EXISTS (
                    SELECT 1 FROM (SELECT name FROM `' . _DB_PREFIX_ . 'hook`) AS existing WHERE name = "' . pSQL($newName) . '"
                  )'
            ) && $result;
        }

        return $result;
    }

    private function ensureAuthorColumn(): bool
    {
        $db = \Db::getInstance();
        $columnExists = (int) $db->getValue(
            'SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "' . _DB_PREFIX_ . 'ever_blog_post"
              AND COLUMN_NAME = "id_author"'
        );

        if ($columnExists > 0) {
            return true;
        }

        return (bool) $db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_post`
             ADD `id_author` int(10) unsigned NOT NULL
             AFTER `id_shop`'
        );
    }

    private function ensureTables(): bool
    {
        $db = \Db::getInstance();
        $sql = [];
        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ever_blog_author` (
                `id_ever_author` int(10) unsigned NOT NULL auto_increment,
                `id_employee` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                `nickhandle` varchar(255) NOT NULL,
                `twitter` varchar(255) DEFAULT NULL,
                `facebook` varchar(255) DEFAULT NULL,
                `linkedin` varchar(255) DEFAULT NULL,
                `date_add` DATETIME DEFAULT NULL,
                `date_upd` DATETIME DEFAULT NULL,
                `indexable` int(10) unsigned DEFAULT NULL,
                `follow` int(10) unsigned DEFAULT NULL,
                `active` int(10) unsigned DEFAULT NULL,
                PRIMARY KEY (`id_ever_author`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ever_blog_author_lang` (
                `id_ever_author` int(10) unsigned NOT NULL,
                `meta_title` varchar(255) DEFAULT NULL,
                `meta_description` varchar(255) DEFAULT NULL,
                `link_rewrite` varchar(255) DEFAULT NULL,
                `content` text NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_ever_author`, `id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_category` (
                `id_ever_post_category` int(10) NOT NULL,
                `id_ever_post` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_ever_post`, `id_ever_post_category`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_tag` (
                `id_ever_post_tag` int(10) NOT NULL,
                `id_ever_post` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_ever_post`, `id_ever_post_tag`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] =
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ever_blog_post_product` (
                `id_ever_post_product` int(10) NOT NULL,
                `id_ever_post` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_ever_post`, `id_ever_post_product`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $result = true;
        foreach ($sql as $query) {
            $result = $db->execute($query) && $result;
        }

        return $result;
    }

    private function ensureAuthorTab(): bool
    {
        $db = \Db::getInstance();
        $existing = (int) $db->getValue(
            'SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE class_name = "AdminEverPsBlogAuthor"'
        );

        if ($existing > 0) {
            return true;
        }

        $parentId = (int) $db->getValue(
            'SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE class_name = "AdminEverPsBlog"'
        );

        $position = (int) $db->getValue(
            'SELECT COALESCE(MAX(position), -1) + 1
            FROM `' . _DB_PREFIX_ . 'tab`
            WHERE id_parent = ' . $parentId
        );

        $inserted = $db->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'tab` (id_parent, position, module, class_name, active)
             VALUES (' . $parentId . ', ' . $position . ', "everpsblog", "AdminEverPsBlogAuthor", 1)'
        );

        if (!$inserted) {
            return false;
        }

        $idTab = (int) $db->Insert_ID();
        $languages = $db->executeS('SELECT id_lang FROM `' . _DB_PREFIX_ . 'lang`') ?: [];
        $result = true;
        foreach ($languages as $language) {
            $result = $db->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'tab_lang` (id_tab, id_lang, name)
                 VALUES (' . $idTab . ', ' . (int) $language['id_lang'] . ', "Authors")'
            ) && $result;
        }

        return $result;
    }

    private function migratePostJsonRelations(): bool
    {
        $db = \Db::getInstance();
        $posts = $db->executeS(
            'SELECT id_ever_post, id_default_category, post_categories, post_tags, post_products
             FROM `' . _DB_PREFIX_ . 'ever_blog_post`'
        ) ?: [];

        $result = true;
        foreach ($posts as $post) {
            $idPost = (int) $post['id_ever_post'];
            $categories = json_decode((string) $post['post_categories'], true) ?: [];
            if ((int) $post['id_default_category'] > 0) {
                $categories[] = (int) $post['id_default_category'];
            }

            $result = $this->insertTaxonomyLinks('ever_blog_post_category', 'id_ever_post_category', $idPost, $categories) && $result;
            $result = $this->insertTaxonomyLinks('ever_blog_post_tag', 'id_ever_post_tag', $idPost, json_decode((string) $post['post_tags'], true) ?: []) && $result;
            $result = $this->insertTaxonomyLinks('ever_blog_post_product', 'id_ever_post_product', $idPost, json_decode((string) $post['post_products'], true) ?: []) && $result;
        }

        return $result;
    }

    /**
     * @param int[] $taxonomyIds
     */
    private function insertTaxonomyLinks(string $table, string $column, int $postId, array $taxonomyIds): bool
    {
        $db = \Db::getInstance();
        $result = true;

        foreach (array_unique(array_map('intval', $taxonomyIds)) as $taxonomyId) {
            if ($taxonomyId <= 0) {
                continue;
            }

            $result = $db->execute(
                'INSERT IGNORE INTO `' . _DB_PREFIX_ . bqSQL($table) . '` (`' . bqSQL($column) . '`, `id_ever_post`)
                 VALUES (' . $taxonomyId . ', ' . $postId . ')'
            ) && $result;
        }

        return $result;
    }
}
