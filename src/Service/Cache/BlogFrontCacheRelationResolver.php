<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service\Cache;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class BlogFrontCacheRelationResolver
{
    /**
     * @return array{author_id:int,default_category_id:int,category_ids:array<int,int>,tag_ids:array<int,int>}
     */
    public function getPostSnapshot(int $postId): array
    {
        $postId = (int) $postId;
        if ($postId <= 0) {
            return $this->emptyPostSnapshot();
        }

        $row = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT `id_author`, `id_default_category`
             FROM `' . _DB_PREFIX_ . 'ever_blog_post`
             WHERE `id_ever_post` = ' . $postId
        );

        return [
            'author_id' => (int) ($row['id_author'] ?? 0),
            'default_category_id' => (int) ($row['id_default_category'] ?? 0),
            'category_ids' => $this->fetchRelationIds('ever_blog_post_category', 'id_ever_post_category', 'id_ever_post', $postId),
            'tag_ids' => $this->fetchRelationIds('ever_blog_post_tag', 'id_ever_post_tag', 'id_ever_post', $postId),
        ];
    }

    /**
     * @return array{parent_id:int}
     */
    public function getCategorySnapshot(int $categoryId): array
    {
        $categoryId = (int) $categoryId;
        if ($categoryId <= 0) {
            return ['parent_id' => 0];
        }

        return [
            'parent_id' => (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT `id_parent_category`
                 FROM `' . _DB_PREFIX_ . 'ever_blog_category`
                 WHERE `id_ever_category` = ' . $categoryId
            ),
        ];
    }

    public function getCommentPostId(int $commentId): int
    {
        $commentId = (int) $commentId;
        if ($commentId <= 0) {
            return 0;
        }

        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT `id_ever_post`
             FROM `' . _DB_PREFIX_ . 'ever_blog_comments`
             WHERE `id_ever_comment` = ' . $commentId
        );
    }

    /**
     * @return int[]
     */
    public function listPostIdsByCategory(int $categoryId): array
    {
        return $this->fetchRelationIds('ever_blog_post_category', 'id_ever_post', 'id_ever_post_category', $categoryId);
    }

    /**
     * @return int[]
     */
    public function listPostIdsByTag(int $tagId): array
    {
        return $this->fetchRelationIds('ever_blog_post_tag', 'id_ever_post', 'id_ever_post_tag', $tagId);
    }

    /**
     * @return int[]
     */
    public function listPostIdsByAuthor(int $authorId): array
    {
        return $this->fetchSingleColumn(
            'SELECT `id_ever_post`
             FROM `' . _DB_PREFIX_ . 'ever_blog_post`
             WHERE `id_author` = ' . (int) $authorId
        );
    }

    /**
     * @return int[]
     */
    public function listPublishedPostIds(int $shopId): array
    {
        return $this->fetchSingleColumn(
            'SELECT DISTINCT p.`id_ever_post`
             FROM `' . _DB_PREFIX_ . 'ever_blog_post` p
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_post_shop` ps
                ON ps.`id_ever_post` = p.`id_ever_post` AND ps.`id_shop` = ' . (int) $shopId . '
             WHERE p.`post_status` = "published"'
        );
    }

    /**
     * @return int[]
     */
    public function listCategoryIds(int $shopId): array
    {
        return $this->fetchSingleColumn(
            'SELECT DISTINCT c.`id_ever_category`
             FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_category_shop` cs
                ON cs.`id_ever_category` = c.`id_ever_category` AND cs.`id_shop` = ' . (int) $shopId
        );
    }

    /**
     * @return int[]
     */
    public function listTagIds(int $shopId): array
    {
        return $this->fetchSingleColumn(
            'SELECT DISTINCT t.`id_ever_tag`
             FROM `' . _DB_PREFIX_ . 'ever_blog_tag` t
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_tag_shop` ts
                ON ts.`id_ever_tag` = t.`id_ever_tag` AND ts.`id_shop` = ' . (int) $shopId
        );
    }

    /**
     * @return int[]
     */
    public function listAuthorIds(int $shopId): array
    {
        return $this->fetchSingleColumn(
            'SELECT DISTINCT a.`id_ever_author`
             FROM `' . _DB_PREFIX_ . 'ever_blog_author` a
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_author_shop` aus
                ON aus.`id_ever_author` = a.`id_ever_author` AND aus.`id_shop` = ' . (int) $shopId
        );
    }

    /**
     * @return array{author_id:int,default_category_id:int,category_ids:array<int,int>,tag_ids:array<int,int>}
     */
    private function emptyPostSnapshot(): array
    {
        return [
            'author_id' => 0,
            'default_category_id' => 0,
            'category_ids' => [],
            'tag_ids' => [],
        ];
    }

    /**
     * @return int[]
     */
    private function fetchRelationIds(string $table, string $selectColumn, string $whereColumn, int $whereValue): array
    {
        return $this->fetchSingleColumn(
            'SELECT `' . bqSQL($selectColumn) . '`
             FROM `' . _DB_PREFIX_ . bqSQL($table) . '`
             WHERE `' . bqSQL($whereColumn) . '` = ' . (int) $whereValue
        );
    }

    /**
     * @return int[]
     */
    private function fetchSingleColumn(string $sql): array
    {
        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($rows)) {
            return [];
        }

        $values = [];
        foreach ($rows as $row) {
            $value = (int) array_shift($row);
            if ($value > 0) {
                $values[] = $value;
            }
        }

        return array_values(array_unique($values));
    }
}
