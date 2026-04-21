<?php

namespace PrestaShop\Module\Everpsblog\Controller\Front;

trait FrontBlogDataProviderTrait
{
    protected function getFrontCategory($idCategory, $idLang, $idShop)
    {
        return $this->frontCacheGet(__METHOD__, [$idCategory, $idLang, $idShop], function () use ($idCategory, $idLang, $idShop) {
            $sql = new \DbQuery();
            $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
            $sql->from('ever_blog_category', 'c');
            $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
            $sql->where('c.id_ever_category = ' . (int) $idCategory);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        });
    }

    protected function getFrontTag($idTag, $idLang, $idShop)
    {
        return $this->frontCacheGet(__METHOD__, [$idTag, $idLang, $idShop], function () use ($idTag, $idLang, $idShop) {
            $sql = new \DbQuery();
            $sql->select('t.*, t.id_ever_tag AS id, tl.title, tl.meta_title, tl.meta_description, tl.link_rewrite, tl.content, tl.bottom_content');
            $sql->from('ever_blog_tag', 't');
            $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $idShop);
            $sql->where('t.id_ever_tag = ' . (int) $idTag);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        });
    }

    protected function getFrontAuthor($idAuthor, $idLang, $idShop)
    {
        return $this->frontCacheGet(__METHOD__, [$idAuthor, $idLang, $idShop], function () use ($idAuthor, $idLang, $idShop) {
            $sql = new \DbQuery();
            $excerptSelect = $this->frontAuthorExcerptColumnExists() ? 'al.excerpt' : '"" AS excerpt';
            $sql->select('a.*, a.id_ever_author AS id, al.meta_title, al.meta_description, al.link_rewrite, ' . $excerptSelect . ', al.content, al.bottom_content');
            $sql->from('ever_blog_author', 'a');
            $sql->innerJoin('ever_blog_author_lang', 'al', 'al.id_ever_author = a.id_ever_author AND al.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_author_shop', 'aus', 'aus.id_ever_author = a.id_ever_author AND aus.id_shop = ' . (int) $idShop);
            $sql->where('a.id_ever_author = ' . (int) $idAuthor);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        });
    }

    protected function getFrontPost($idPost, $idLang, $idShop)
    {
        return $this->frontCacheGet(__METHOD__, [$idPost, $idLang, $idShop], function () use ($idPost, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->where('p.id_ever_post = ' . (int) $idPost);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        });
    }

    protected function countFrontPostsByCategory($idCategory, $idLang, $idShop)
    {
        return (int) $this->frontCacheGet(__METHOD__, [$idCategory, $idLang, $idShop], function () use ($idCategory, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
            $sql->where('pc.id_ever_post_category = ' . (int) $idCategory);

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        });
    }

    protected function countFrontPostsByTag($idTag, $idLang, $idShop)
    {
        return (int) $this->frontCacheGet(__METHOD__, [$idTag, $idLang, $idShop], function () use ($idTag, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->innerJoin('ever_blog_post_tag', 'pt', 'pt.id_ever_post = p.id_ever_post');
            $sql->where('pt.id_ever_post_tag = ' . (int) $idTag);

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        });
    }

    protected function countFrontPostsByAuthor($idAuthor, $idLang, $idShop)
    {
        return (int) $this->frontCacheGet(__METHOD__, [$idAuthor, $idLang, $idShop], function () use ($idAuthor, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->where('p.id_author = ' . (int) $idAuthor);

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        });
    }

    protected function getFrontPostsByCategory($idLang, $idShop, $idCategory, $start = 0, $limit = null)
    {
        return $this->frontCacheGet(__METHOD__, [$idLang, $idShop, $idCategory, $start, $limit], function () use ($idLang, $idShop, $idCategory, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
            $sql->where('pc.id_ever_post_category = ' . (int) $idCategory);

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        });
    }

    protected function getFrontPostsByTag($idLang, $idShop, $idTag, $start = 0, $limit = null)
    {
        return $this->frontCacheGet(__METHOD__, [$idLang, $idShop, $idTag, $start, $limit], function () use ($idLang, $idShop, $idTag, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->innerJoin('ever_blog_post_tag', 'pt', 'pt.id_ever_post = p.id_ever_post');
            $sql->where('pt.id_ever_post_tag = ' . (int) $idTag);

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        });
    }

    protected function getFrontPostsByAuthor($idLang, $idShop, $idAuthor, $start = 0, $limit = null)
    {
        return $this->frontCacheGet(__METHOD__, [$idLang, $idShop, $idAuthor, $start, $limit], function () use ($idLang, $idShop, $idAuthor, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->where('p.id_author = ' . (int) $idAuthor);

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        });
    }

    protected function getFrontLatestPosts($idLang, $idShop, $start = 0, $limit = null)
    {
        return $this->frontCacheGet(__METHOD__, [$idLang, $idShop, $start, $limit], function () use ($idLang, $idShop, $start, $limit) {
            return $this->executeFrontPostsQuery($this->createFrontPostQuery($idLang, $idShop), $start, $limit);
        });
    }

    protected function getFilteredFrontPosts($idLang, $idShop, $idCategory = null, $idTag = null, $start = 0, $limit = null)
    {
        return $this->frontCacheGet(__METHOD__, [$idLang, $idShop, $idCategory, $idTag, $start, $limit], function () use ($idLang, $idShop, $idCategory, $idTag, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            if ($idCategory) {
                $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
                $sql->where('pc.id_ever_post_category = ' . (int) $idCategory);
            }
            if ($idTag) {
                $sql->innerJoin('ever_blog_post_tag', 'pt', 'pt.id_ever_post = p.id_ever_post');
                $sql->where('pt.id_ever_post_tag = ' . (int) $idTag);
            }

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        });
    }

    protected function countFrontPostsBySearch($query, $idLang, $idShop)
    {
        return (int) $this->frontCacheGet(__METHOD__, [$query, $idLang, $idShop], function () use ($query, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->where('pl.title LIKE "%' . pSQL((string) $query) . '%" OR pl.content LIKE "%' . pSQL((string) $query) . '%" OR pl.excerpt LIKE "%' . pSQL((string) $query) . '%"');

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        });
    }

    protected function searchFrontPosts($query, $idLang, $idShop, $start = 0, $limit = null)
    {
        return $this->frontCacheGet(__METHOD__, [$query, $idLang, $idShop, $start, $limit], function () use ($query, $idLang, $idShop, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->where('pl.title LIKE "%' . pSQL((string) $query) . '%" OR pl.content LIKE "%' . pSQL((string) $query) . '%" OR pl.excerpt LIKE "%' . pSQL((string) $query) . '%"');

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        });
    }

    protected function getFrontCommentsByEmail($email, $idLang)
    {
        return $this->frontCacheGet(__METHOD__, [$email, $idLang], function () use ($email, $idLang) {
            $sql = new \DbQuery();
            $sql->select('*, id_ever_comment AS id');
            $sql->from('ever_blog_comments');
            $sql->where('user_email = "' . pSQL((string) $email) . '"');
            $sql->where('id_lang = ' . (int) $idLang);
            $sql->orderBy('date_add DESC');

            return $this->frontRowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        });
    }

    protected function getFrontChildrenCategories($idParentCategory, $idLang, $idShop)
    {
        return $this->frontCacheGet(__METHOD__, [$idParentCategory, $idLang, $idShop], function () use ($idParentCategory, $idLang, $idShop) {
            $sql = new \DbQuery();
            $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
            $sql->from('ever_blog_category', 'c');
            $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
            $sql->where('c.id_parent_category = ' . (int) $idParentCategory);
            $sql->orderBy('cl.title ASC');

            return $this->frontRowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        });
    }

    protected function frontCategoryHasChildren($idCategory)
    {
        return (bool) $this->frontCacheGet(__METHOD__, [$idCategory], function () use ($idCategory) {
            $sql = new \DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('ever_blog_category');
            $sql->where('id_parent_category = ' . (int) $idCategory);

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        });
    }

    protected function incrementFrontTaxonomyCount($table, $primary, $id)
    {
        return (bool) \Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . bqSQL($table) . '`
            SET `count` = `count` + 1
            WHERE `' . bqSQL($primary) . '` = ' . (int) $id
        );
    }

    protected function frontRowToObject($row)
    {
        if (!is_array($row)) {
            return new \stdClass();
        }

        return (object) $row;
    }

    protected function frontRowsToObjects($rows)
    {
        if (!is_array($rows)) {
            return [];
        }

        return array_map(function ($row) {
            return (object) $row;
        }, $rows);
    }

    private function createFrontPostQuery($idLang, $idShop, $select = null)
    {
        $sql = new \DbQuery();
        $sql->select($select ?: 'p.id_ever_post, p.id_ever_post AS id, p.id_shop, p.id_author, p.id_author AS id_ever_author, p.id_default_category, p.post_status, p.date_add, p.date_upd, p.indexable, p.follow, p.sitemap, p.active, p.allowed_groups, p.post_categories, p.post_tags, p.post_products, p.psswd, p.starred, p.count, p.groups, pl.title AS title, pl.meta_title AS meta_title, pl.meta_description AS meta_description, pl.link_rewrite AS link_rewrite, pl.content AS content, pl.excerpt AS excerpt');
        $sql->from('ever_blog_post', 'p');
        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $idLang);
        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $idShop);
        $sql->where('p.post_status = "published"');

        return $sql;
    }

    private function frontCacheGet($method, array $parts, $resolver)
    {
        $key = $this->frontCacheKey($method, $parts);
        if (\Cache::isStored($key)) {
            return \Cache::retrieve($key);
        }

        $value = $resolver();
        \Cache::store($key, $value);

        return $value;
    }

    private function frontCacheKey($method, array $parts)
    {
        return 'everpsblog.front.' . str_replace('\\', '.', (string) $method) . '.' . md5(json_encode($parts));
    }

    private function frontAuthorExcerptColumnExists()
    {
        return (bool) $this->frontCacheGet(__METHOD__, [], function () {
            return (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                'DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_author_lang` `excerpt`'
            );
        });
    }

    private function executeFrontPostsQuery(\DbQuery $sql, $start, $limit)
    {
        $limit = null === $limit ? (int) \Configuration::get('EVERPSBLOG_PAGINATION') : (int) $limit;
        if ($limit <= 0) {
            $limit = 10;
        }
        $sql->orderBy('p.date_add DESC, p.id_ever_post DESC');
        $sql->limit($limit, (int) $start);

        return $this->frontCacheGet(__METHOD__, [(string) $sql], function () use ($sql) {
            $posts = $this->frontRowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
            foreach ($posts as $post) {
                $postId = (int) ($post->id ?? $post->id_ever_post ?? 0);
                if ($postId <= 0) {
                    continue;
                }
                $post->url = $this->context->link->getModuleLink(
                    $this->module->name,
                    'post',
                    [
                        'id_ever_post' => $postId,
                        'link_rewrite' => (string) ($post->link_rewrite ?? ''),
                    ]
                );
                $post->featured_image = $this->getBlogImageService()->getBlogImageUrl($postId, (int) $this->context->shop->id, 'post');
                $post->featured_thumb = $this->getBlogImageService()->getBlogThumbUrl($postId, (int) $this->context->shop->id, 'post');
                $post->cover = $post->featured_thumb;
                $post->summary = $this->frontPostSummary($post);
            }

            return $posts;
        });
    }

    private function frontPostSummary($post)
    {
        $summary = '';
        if (isset($post->excerpt) && trim((string) $post->excerpt) !== '') {
            $summary = (string) $post->excerpt;
        } elseif (isset($post->meta_description) && trim((string) $post->meta_description) !== '') {
            $summary = (string) $post->meta_description;
        } elseif (isset($post->content)) {
            $summary = strip_tags((string) $post->content);
        }

        if (function_exists('mb_substr')) {
            return mb_substr(trim($summary), 0, 300);
        }

        return substr(trim($summary), 0, 300);
    }
}
