<?php

namespace PrestaShop\Module\Everpsblog\Service;

use Psr\Cache\CacheItemPoolInterface;

class BlogTaxonomyService
{
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    private function cacheKey($suffix)
    {
        return 'everpsblog.taxonomy.' . md5($suffix);
    }

    private function clearCache()
    {
        $this->cache->clear();
    }

    public function insert($idElement, $idPost, $taxonomy)
    {
        $map = [
            'category' => ['table' => _DB_PREFIX_ . 'ever_blog_post_category', 'key' => 'id_ever_post_category'],
            'tag' => ['table' => _DB_PREFIX_ . 'ever_blog_post_tag', 'key' => 'id_ever_post_tag'],
            'product' => ['table' => _DB_PREFIX_ . 'ever_blog_post_product', 'key' => 'id_ever_post_product'],
        ];
        if (!isset($map[$taxonomy]) || !$this->postExists((int) $idPost) || !$this->taxonomyExists((int) $idElement, $taxonomy)) {
            return false;
        }

        $sql = 'INSERT IGNORE INTO `' . pSQL($map[$taxonomy]['table']) . '` (`' . pSQL($map[$taxonomy]['key']) . '`, id_ever_post)
                VALUES (' . (int) $idElement . ', ' . (int) $idPost . ')';
        $result = (bool) \Db::getInstance()->execute($sql);
        $this->clearCache();

        return $result;
    }

    public function dropPostTaxonomies($postId)
    {
        $ok = $this->dropTaxonomy((int) $postId, 'category');
        $ok = $this->dropTaxonomy((int) $postId, 'tag') && $ok;
        $ok = $this->dropTaxonomy((int) $postId, 'product') && $ok;
        $this->clearCache();

        return $ok;
    }

    public function dropCategoryTaxonomy($categoryId)
    {
        $result = (bool) \Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ever_blog_post_category` WHERE id_ever_post_category = ' . (int) $categoryId);
        $this->clearCache();

        return $result;
    }

    public function dropTagTaxonomy($tagId)
    {
        $result = (bool) \Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ever_blog_post_tag` WHERE id_ever_post_tag = ' . (int) $tagId);
        $this->clearCache();

        return $result;
    }

    public function dropProductTaxonomy($productId)
    {
        $result = (bool) \Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ever_blog_post_product` WHERE id_ever_post_product = ' . (int) $productId);
        $this->clearCache();

        return $result;
    }

    public function getPostTagsTaxonomies($postId)
    {
        return $this->getPostTaxonomyIds((int) $postId, 'ever_blog_post_tag', 'id_ever_post_tag');
    }

    public function getPostCategoriesTaxonomies($postId)
    {
        $key = $this->cacheKey('post.categories.' . (int) $postId);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $sql = new \DbQuery();
        $sql->select('epc.id_ever_post_category');
        $sql->from('ever_blog_post_category', 'epc');
        $sql->leftJoin('ever_blog_category', 'bc', 'bc.id_ever_category = epc.id_ever_post_category');
        $sql->where('epc.id_ever_post = ' . (int) $postId);
        $sql->orderBy('bc.id_parent_category ASC');
        $sql->groupBy('bc.id_ever_category');

        $rows = \Db::getInstance()->executeS($sql) ?: [];
        $result = array_map('intval', array_column($rows, 'id_ever_post_category'));
        $item->set($result);
        $this->cache->save($item);

        return $result;
    }

    public function getPostProductsTaxonomies($postId)
    {
        return $this->getPostTaxonomyIds((int) $postId, 'ever_blog_post_product', 'id_ever_post_product');
    }

    public function getCategoryParentsTaxonomy($categoryId, $active = 1)
    {
        $key = $this->cacheKey('category.parents.' . (int) $categoryId . '.' . (int) $active);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $taxonomies = [];
        $rootCategory = \EverPsBlogCategory::getRootCategory();
        $sql = new \DbQuery();
        $sql->select('id_parent_category');
        $sql->from('ever_blog_category');
        $sql->where('id_ever_category = ' . (int) $categoryId);
        $sql->where('active = ' . (int) $active);
        $taxonomy = (int) \Db::getInstance()->getValue($sql);

        if ($taxonomy > 0) {
            $taxonomies[] = $taxonomy;
            $category = new \EverPsBlogCategory($taxonomy);
            if ((int) $category->id_parent_category > 0 && (int) $rootCategory->id !== (int) $category->id_parent_category) {
                $taxonomies[] = (int) $category->id_parent_category;
            }
        }

        $item->set($taxonomies);
        $this->cache->save($item);

        return $taxonomies;
    }

    public function checkDefaultPostCategory($postId)
    {
        $taxonomies = $this->getPostCategoriesTaxonomies((int) $postId);
        if (empty($taxonomies)) {
            $rootCategory = \EverPsBlogCategory::getRootCategory();
            $this->insert((int) $rootCategory->id, (int) $postId, 'category');
        }
    }

    public function migrateJsonPostsData()
    {
        $sql = new \DbQuery();
        $sql->select('id_ever_post, id_default_category, post_categories, post_tags, post_products');
        $sql->from('ever_blog_post');
        $posts = \Db::getInstance()->executeS($sql) ?: [];

        foreach ($posts as $postArray) {
            $idPost = (int) $postArray['id_ever_post'];
            $categories = json_decode((string) $postArray['post_categories'], true) ?: [];
            if ((int) $postArray['id_default_category'] > 0) {
                $categories[] = (int) $postArray['id_default_category'];
            }
            foreach (array_unique(array_map('intval', $categories)) as $idCategory) {
                $this->insert($idCategory, $idPost, 'category');
            }
            foreach (array_unique(array_map('intval', json_decode((string) $postArray['post_tags'], true) ?: [])) as $idTag) {
                $this->insert($idTag, $idPost, 'tag');
            }
            foreach (array_unique(array_map('intval', json_decode((string) $postArray['post_products'], true) ?: [])) as $idProduct) {
                $this->insert($idProduct, $idPost, 'product');
            }
        }

        return true;
    }

    private function getPostTaxonomyIds($postId, $table, $column)
    {
        $key = $this->cacheKey('post.' . $table . '.' . (int) $postId);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $sql = new \DbQuery();
        $sql->select($column);
        $sql->from($table);
        $sql->where('id_ever_post = ' . (int) $postId);
        $rows = \Db::getInstance()->executeS($sql) ?: [];
        $result = array_map('intval', array_column($rows, $column));
        $item->set($result);
        $this->cache->save($item);

        return $result;
    }

    private function postExists($postId)
    {
        $sql = new \DbQuery();
        $sql->select('id_ever_post');
        $sql->from('ever_blog_post');
        $sql->where('id_ever_post = ' . (int) $postId);

        return (bool) \Db::getInstance()->getValue($sql);
    }

    private function taxonomyExists($idObj, $taxonomy)
    {
        $map = [
            'category' => ['table' => 'ever_blog_category', 'key' => 'id_ever_category'],
            'tag' => ['table' => 'ever_blog_tag', 'key' => 'id_ever_tag'],
            'product' => ['table' => 'product', 'key' => 'id_product'],
        ];
        if (!isset($map[$taxonomy])) {
            return false;
        }

        $sql = new \DbQuery();
        $sql->select($map[$taxonomy]['key']);
        $sql->from($map[$taxonomy]['table']);
        $sql->where($map[$taxonomy]['key'] . ' = ' . (int) $idObj);

        return (bool) \Db::getInstance()->getValue($sql);
    }

    private function dropTaxonomy($postId, $taxonomy)
    {
        $tables = [
            'category' => _DB_PREFIX_ . 'ever_blog_post_category',
            'tag' => _DB_PREFIX_ . 'ever_blog_post_tag',
            'product' => _DB_PREFIX_ . 'ever_blog_post_product',
        ];
        if (!isset($tables[$taxonomy])) {
            return false;
        }

        return (bool) \Db::getInstance()->execute('DELETE FROM ' . pSQL($tables[$taxonomy]) . ' WHERE id_ever_post = ' . (int) $postId);
    }
}
