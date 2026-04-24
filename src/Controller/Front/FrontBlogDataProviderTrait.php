<?php

namespace PrestaShop\Module\Everpsblog\Controller\Front;

use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheTags;

trait FrontBlogDataProviderTrait
{
    protected function getFrontCategory($idCategory, $idLang, $idShop): \stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idCategory, $idLang, $idShop], function () use ($idCategory, $idLang, $idShop) {
            $sql = new \DbQuery();
            $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
            $sql->from('ever_blog_category', 'c');
            $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
            $sql->where('c.id_ever_category = ' . (int) $idCategory);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::category((int) $idCategory)]);
    }

    protected function getFrontTag($idTag, $idLang, $idShop): \stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idTag, $idLang, $idShop], function () use ($idTag, $idLang, $idShop) {
            $sql = new \DbQuery();
            $sql->select('t.*, t.id_ever_tag AS id, tl.title, tl.meta_title, tl.meta_description, tl.link_rewrite, tl.content, tl.bottom_content');
            $sql->from('ever_blog_tag', 't');
            $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $idShop);
            $sql->where('t.id_ever_tag = ' . (int) $idTag);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::tag((int) $idTag)]);
    }

    protected function getFrontAuthor($idAuthor, $idLang, $idShop): \stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idAuthor, $idLang, $idShop], function () use ($idAuthor, $idLang, $idShop) {
            $sql = new \DbQuery();
            $excerptSelect = $this->frontAuthorExcerptColumnExists() ? 'al.excerpt' : '"" AS excerpt';
            $sql->select('a.*, a.id_ever_author AS id, al.meta_title, al.meta_description, al.link_rewrite, ' . $excerptSelect . ', al.content, al.bottom_content');
            $sql->from('ever_blog_author', 'a');
            $sql->innerJoin('ever_blog_author_lang', 'al', 'al.id_ever_author = a.id_ever_author AND al.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_author_shop', 'aus', 'aus.id_ever_author = a.id_ever_author AND aus.id_shop = ' . (int) $idShop);
            $sql->where('a.id_ever_author = ' . (int) $idAuthor);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::author((int) $idAuthor)]);
    }

    protected function getFrontPost($idPost, $idLang, $idShop): \stdClass
    {
        return $this->frontCacheRemember(__METHOD__, [$idPost, $idLang, $idShop], function () use ($idPost, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->where('p.id_ever_post = ' . (int) $idPost);

            return $this->frontRowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
        }, [BlogFrontCacheTags::post((int) $idPost)]);
    }

    protected function countFrontPostsByCategory($idCategory, $idLang, $idShop)
    {
        return (int) $this->frontCacheRemember(__METHOD__, [$idCategory, $idLang, $idShop], function () use ($idCategory, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
            $sql->where('pc.id_ever_post_category = ' . (int) $idCategory);

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }, [BlogFrontCacheTags::category((int) $idCategory)]);
    }

    protected function countFrontPostsByTag($idTag, $idLang, $idShop)
    {
        return (int) $this->frontCacheRemember(__METHOD__, [$idTag, $idLang, $idShop], function () use ($idTag, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->innerJoin('ever_blog_post_tag', 'pt', 'pt.id_ever_post = p.id_ever_post');
            $sql->where('pt.id_ever_post_tag = ' . (int) $idTag);

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }, [BlogFrontCacheTags::tag((int) $idTag)]);
    }

    protected function countFrontPostsByAuthor($idAuthor, $idLang, $idShop)
    {
        return (int) $this->frontCacheRemember(__METHOD__, [$idAuthor, $idLang, $idShop], function () use ($idAuthor, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->where('p.id_author = ' . (int) $idAuthor);

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }, [BlogFrontCacheTags::author((int) $idAuthor)]);
    }

    /**
     * @return list<\stdClass>
     */
    protected function getFrontPostsByCategory($idLang, $idShop, $idCategory, $start = 0, $limit = null): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop, $idCategory, $start, $limit], function () use ($idLang, $idShop, $idCategory, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
            $sql->where('pc.id_ever_post_category = ' . (int) $idCategory);

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        }, [BlogFrontCacheTags::category((int) $idCategory)], function ($posts) {
            return $this->frontExtractEntityTags($posts, 'post', ['id', 'id_ever_post']);
        });
    }

    /**
     * @return list<\stdClass>
     */
    protected function getFrontPostsByTag($idLang, $idShop, $idTag, $start = 0, $limit = null): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop, $idTag, $start, $limit], function () use ($idLang, $idShop, $idTag, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->innerJoin('ever_blog_post_tag', 'pt', 'pt.id_ever_post = p.id_ever_post');
            $sql->where('pt.id_ever_post_tag = ' . (int) $idTag);

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        }, [BlogFrontCacheTags::tag((int) $idTag)], function ($posts) {
            return $this->frontExtractEntityTags($posts, 'post', ['id', 'id_ever_post']);
        });
    }

    /**
     * @return list<\stdClass>
     */
    protected function getFrontPostsByAuthor($idLang, $idShop, $idAuthor, $start = 0, $limit = null): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop, $idAuthor, $start, $limit], function () use ($idLang, $idShop, $idAuthor, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->where('p.id_author = ' . (int) $idAuthor);

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        }, [BlogFrontCacheTags::author((int) $idAuthor)], function ($posts) {
            return $this->frontExtractEntityTags($posts, 'post', ['id', 'id_ever_post']);
        });
    }

    /**
     * @return list<\stdClass>
     */
    protected function getFrontLatestPosts($idLang, $idShop, $start = 0, $limit = null): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop, $start, $limit], function () use ($idLang, $idShop, $start, $limit) {
            return $this->executeFrontPostsQuery($this->createFrontPostQuery($idLang, $idShop), $start, $limit);
        }, [BlogFrontCacheTags::BLOG_LISTING], function ($posts) {
            return $this->frontExtractEntityTags($posts, 'post', ['id', 'id_ever_post']);
        });
    }

    /**
     * @return list<\stdClass>
     */
    protected function getFilteredFrontPosts($idLang, $idShop, $idCategory = null, $idTag = null, $start = 0, $limit = null): array
    {
        $tags = [BlogFrontCacheTags::BLOG_LISTING];
        if ($idCategory) {
            $tags[] = BlogFrontCacheTags::category((int) $idCategory);
        }
        if ($idTag) {
            $tags[] = BlogFrontCacheTags::tag((int) $idTag);
        }

        return $this->frontCacheRemember(__METHOD__, [$idLang, $idShop, $idCategory, $idTag, $start, $limit], function () use ($idLang, $idShop, $idCategory, $idTag, $start, $limit) {
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
        }, $tags, function ($posts) {
            return $this->frontExtractEntityTags($posts, 'post', ['id', 'id_ever_post']);
        });
    }

    protected function countFrontPostsBySearch($query, $idLang, $idShop)
    {
        return (int) $this->frontCacheRemember(__METHOD__, [$query, $idLang, $idShop], function () use ($query, $idLang, $idShop) {
            $sql = $this->createFrontPostQuery($idLang, $idShop, 'COUNT(DISTINCT p.id_ever_post)');
            $sql->where('pl.title LIKE "%' . pSQL((string) $query) . '%" OR pl.content LIKE "%' . pSQL((string) $query) . '%" OR pl.excerpt LIKE "%' . pSQL((string) $query) . '%"');

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }, [BlogFrontCacheTags::BLOG_SEARCH]);
    }

    /**
     * @return list<\stdClass>
     */
    protected function searchFrontPosts($query, $idLang, $idShop, $start = 0, $limit = null): array
    {
        return $this->frontCacheRemember(__METHOD__, [$query, $idLang, $idShop, $start, $limit], function () use ($query, $idLang, $idShop, $start, $limit) {
            $sql = $this->createFrontPostQuery($idLang, $idShop);
            $sql->where('pl.title LIKE "%' . pSQL((string) $query) . '%" OR pl.content LIKE "%' . pSQL((string) $query) . '%" OR pl.excerpt LIKE "%' . pSQL((string) $query) . '%"');

            return $this->executeFrontPostsQuery($sql, $start, $limit);
        }, [BlogFrontCacheTags::BLOG_SEARCH], function ($posts) {
            return $this->frontExtractEntityTags($posts, 'post', ['id', 'id_ever_post']);
        });
    }

    /**
     * @return list<\stdClass>
     */
    protected function getFrontCommentsByEmail($email, $idLang): array
    {
        return $this->frontCacheRemember(__METHOD__, [$email, $idLang], function () use ($email, $idLang) {
            $sql = new \DbQuery();
            $sql->select('*, id_ever_comment AS id');
            $sql->from('ever_blog_comments');
            $sql->where('user_email = "' . pSQL((string) $email) . '"');
            $sql->where('id_lang = ' . (int) $idLang);
            $sql->orderBy('date_add DESC');

            return $this->frontRowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        }, [BlogFrontCacheTags::BLOG_COMMENTS_CUSTOMER], function ($comments) {
            return $this->frontExtractEntityTags($comments, 'comment', ['id', 'id_ever_comment']);
        });
    }

    /**
     * @return list<\stdClass>
     */
    protected function getFrontChildrenCategories($idParentCategory, $idLang, $idShop): array
    {
        return $this->frontCacheRemember(__METHOD__, [$idParentCategory, $idLang, $idShop], function () use ($idParentCategory, $idLang, $idShop) {
            $sql = new \DbQuery();
            $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
            $sql->from('ever_blog_category', 'c');
            $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $idLang);
            $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $idShop);
            $sql->where('c.id_parent_category = ' . (int) $idParentCategory);
            $sql->where('c.id_ever_category != ' . (int) $idParentCategory);
            $sql->where('COALESCE(c.is_root_category, 0) = 0');
            $sql->where('c.active = 1');
            $sql->orderBy('cl.title ASC');

            return $this->frontRowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        }, [BlogFrontCacheTags::category((int) $idParentCategory)], function ($categories) {
            return $this->frontExtractEntityTags($categories, 'category', ['id', 'id_ever_category']);
        });
    }

    protected function frontCategoryHasChildren($idCategory)
    {
        return (bool) $this->frontCacheRemember(__METHOD__, [$idCategory], function () use ($idCategory) {
            $sql = new \DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('ever_blog_category');
            $sql->where('id_parent_category = ' . (int) $idCategory);
            $sql->where('id_ever_category != ' . (int) $idCategory);
            $sql->where('COALESCE(is_root_category, 0) = 0');
            $sql->where('active = 1');

            return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        }, [BlogFrontCacheTags::category((int) $idCategory)]);
    }

    protected function incrementFrontTaxonomyCount($table, $primary, $id)
    {
        return (bool) \Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . bqSQL($table) . '`
            SET `count` = `count` + 1
            WHERE `' . bqSQL($primary) . '` = ' . (int) $id
        );
    }

    protected function getFrontLinkedProductViewData($productIds, $idLang, $idShop): array
    {
        $products = $this->presentFrontProducts($this->normalizeFrontProductIds($productIds), (int) $idLang, (int) $idShop);
        $countProducts = count($products);

        return [
            'count_products' => $countProducts,
            'ps_products' => $products,
            'ps_products_chunks' => $countProducts > 0 ? array_chunk($products, 4) : [],
        ];
    }

    private function normalizeFrontProductIds($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }

    private function presentFrontProducts(array $productIds, int $idLang, int $idShop): array
    {
        if (empty($productIds)) {
            return [];
        }

        $assembler = new \ProductAssembler($this->context);
        $presenterFactory = new \ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presentationSettings->showPrices = true;

        $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
            new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($this->context->link),
            $this->context->link,
            new \PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
            new \PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products = [];
        foreach ($productIds as $productId) {
            $product = new \Product((int) $productId, true, (int) $idLang, (int) $idShop);
            if (!\Validate::isLoadedObject($product) || !\Product::checkAccessStatic((int) $product->id, false)) {
                continue;
            }

            $cover = \Product::getCover((int) $product->id);
            if (is_array($cover) && isset($cover['id_image'])) {
                $product->cover = (int) $cover['id_image'];
            }

            $products[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct(['id_product' => (int) $product->id]),
                $this->context->language
            );
        }

        return $products;
    }

    protected function frontRowToObject($row): \stdClass
    {
        if (!is_array($row)) {
            return new \stdClass();
        }

        return (object) $row;
    }

    /**
     * @return list<\stdClass>
     */
    protected function frontRowsToObjects($rows): array
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

    private function frontAuthorExcerptColumnExists()
    {
        return (bool) $this->frontCacheRemember(__METHOD__, [], function () {
            return (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                'DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_author_lang` `excerpt`'
            );
        });
    }

    /**
     * @return list<\stdClass>
     */
    private function executeFrontPostsQuery(\DbQuery $sql, $start, $limit): array
    {
        $limit = null === $limit ? (int) \Configuration::get('EVERPSBLOG_PAGINATION') : (int) $limit;
        if ($limit <= 0) {
            $limit = 10;
        }
        $sql->orderBy('p.date_add DESC, p.id_ever_post DESC');
        $sql->limit($limit, (int) $start);

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
