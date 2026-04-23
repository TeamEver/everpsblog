<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service\Cache;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Everpsblog\Service\BlogImageService;

final class BlogFrontCacheWarmer
{
    private const TRAIT_SCOPE = 'PrestaShop\\Module\\Everpsblog\\Controller\\Front\\FrontBlogDataProviderTrait::';
    private const BLOG_SCOPE = 'EverPsBlogblogModuleFrontController::';
    private const POST_SCOPE = 'EverPsBlogpostModuleFrontController::';

    /** @var BlogFrontCache */
    private $cache;

    /** @var BlogFrontCacheRelationResolver */
    private $resolver;

    /** @var BlogImageService */
    private $blogImageService;

    public function __construct(
        ?BlogFrontCache $cache = null,
        ?BlogFrontCacheRelationResolver $resolver = null,
        ?BlogImageService $blogImageService = null
    ) {
        $this->cache = $cache ?: new BlogFrontCache();
        $this->resolver = $resolver ?: new BlogFrontCacheRelationResolver();
        $this->blogImageService = $blogImageService ?: new BlogImageService();
    }

    /**
     * @return array<string, int>
     */
    public function warm(int $shopId, int $langId, int $pageLimit = 3): array
    {
        $pageLimit = max(1, $pageLimit);
        $perPage = max(1, (int) \Configuration::get('EVERPSBLOG_PAGINATION'));
        $stats = [
            'blog_pages' => 0,
            'categories' => 0,
            'tags' => 0,
            'authors' => 0,
            'posts' => 0,
        ];

        $totalPosts = $this->warmBlogHome($shopId, $langId, $pageLimit, $perPage, $stats);
        $this->warmCategories($shopId, $langId, $pageLimit, $perPage, $stats);
        $this->warmTags($shopId, $langId, $pageLimit, $perPage, $stats);
        $this->warmAuthors($shopId, $langId, $pageLimit, $perPage, $stats);
        $this->warmPosts($shopId, $langId, $stats);

        if ($totalPosts > 0) {
            $stats['blog_pages'] = max($stats['blog_pages'], min($pageLimit, (int) ceil($totalPosts / $perPage)));
        }

        return $stats;
    }

    /**
     * @param array<string, int> $stats
     */
    private function warmBlogHome(int $shopId, int $langId, int $pageLimit, int $perPage, array &$stats): int
    {
        $totalPosts = (int) $this->warmRemember(
            self::BLOG_SCOPE . 'getPostRowsCount',
            [$langId, $shopId],
            function () use ($langId, $shopId) {
                $sql = new \DbQuery();
                $sql->select('COUNT(DISTINCT p.id_ever_post)');
                $sql->from('ever_blog_post', 'p');
                $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $langId);
                $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $shopId);
                $sql->where('p.post_status = "published"');
                $sql->where('p.active = 1');

                return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            },
            [BlogFrontCacheTags::BLOG_LISTING]
        );

        $this->warmRemember(
            self::BLOG_SCOPE . 'getFrontLocalizedCategories',
            [$langId, $shopId],
            function () use ($langId, $shopId) {
                $sql = new \DbQuery();
                $sql->select('c.id_ever_category, c.is_root_category, cl.title, cl.link_rewrite');
                $sql->from('ever_blog_category', 'c');
                $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $langId);
                $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $shopId);
                $sql->where('c.active = 1');
                $sql->orderBy('cl.title ASC');

                return \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
            },
            [BlogFrontCacheTags::BLOG_CATEGORIES_INDEX],
            function ($rows) {
                return $this->extractEntityTags($rows, 'category', ['id_ever_category', 'id']);
            }
        );

        $this->warmRemember(
            self::BLOG_SCOPE . 'getFrontLocalizedTags',
            [$langId, $shopId],
            function () use ($langId, $shopId) {
                $sql = new \DbQuery();
                $sql->select('t.id_ever_tag, tl.title, tl.link_rewrite');
                $sql->from('ever_blog_tag', 't');
                $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $langId);
                $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $shopId);
                $sql->where('t.active = 1');
                $sql->orderBy('tl.title ASC');

                return \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
            },
            [BlogFrontCacheTags::BLOG_TAGS_INDEX],
            function ($rows) {
                return $this->extractEntityTags($rows, 'tag', ['id_ever_tag', 'id']);
            }
        );

        $pageCount = max(1, min($pageLimit, (int) ceil(max(1, $totalPosts) / $perPage)));
        for ($page = 0; $page < $pageCount; ++$page) {
            $start = $page * $perPage;
            $this->warmBlogRows($langId, $shopId, $start, $perPage, null);
            $this->warmBlogRows($langId, $shopId, $start, $perPage, true);
        }

        $this->warmRemember(
            self::TRAIT_SCOPE . 'getFrontLatestPosts',
            [$langId, $shopId, 0, $perPage],
            function () use ($langId, $shopId, $perPage) {
                $sql = $this->createTraitPostQuery($langId, $shopId);

                return $this->executeTraitPostsQuery($sql, $shopId, 0, $perPage);
            },
            [BlogFrontCacheTags::BLOG_LISTING],
            function ($posts) {
                return $this->extractEntityTags($posts, 'post', ['id', 'id_ever_post']);
            }
        );

        return $totalPosts;
    }

    /**
     * @param array<string, int> $stats
     */
    private function warmCategories(int $shopId, int $langId, int $pageLimit, int $perPage, array &$stats): void
    {
        foreach ($this->resolver->listCategoryIds($shopId) as $categoryId) {
            ++$stats['categories'];

            $this->warmRemember(
                self::TRAIT_SCOPE . 'getFrontCategory',
                [$categoryId, $langId, $shopId],
                function () use ($categoryId, $langId, $shopId) {
                    $sql = new \DbQuery();
                    $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
                    $sql->from('ever_blog_category', 'c');
                    $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $langId);
                    $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $shopId);
                    $sql->where('c.id_ever_category = ' . (int) $categoryId);

                    return $this->rowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
                },
                [BlogFrontCacheTags::category($categoryId)]
            );

            $totalPosts = (int) $this->warmRemember(
                self::TRAIT_SCOPE . 'countFrontPostsByCategory',
                [$categoryId, $langId, $shopId],
                function () use ($categoryId, $langId, $shopId) {
                    $sql = $this->createTraitPostQuery($langId, $shopId, 'COUNT(DISTINCT p.id_ever_post)');
                    $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
                    $sql->where('pc.id_ever_post_category = ' . (int) $categoryId);

                    return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                },
                [BlogFrontCacheTags::category($categoryId)]
            );

            $pageCount = max(1, min($pageLimit, (int) ceil(max(1, $totalPosts) / $perPage)));
            for ($page = 0; $page < $pageCount; ++$page) {
                $start = $page * $perPage;
                $this->warmRemember(
                    self::TRAIT_SCOPE . 'getFrontPostsByCategory',
                    [$langId, $shopId, $categoryId, $start, $perPage],
                    function () use ($langId, $shopId, $categoryId, $start, $perPage) {
                        $sql = $this->createTraitPostQuery($langId, $shopId);
                        $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
                        $sql->where('pc.id_ever_post_category = ' . (int) $categoryId);

                        return $this->executeTraitPostsQuery($sql, $shopId, $start, $perPage);
                    },
                    [BlogFrontCacheTags::category($categoryId)],
                    function ($posts) {
                        return $this->extractEntityTags($posts, 'post', ['id', 'id_ever_post']);
                    }
                );
            }

            $this->warmRemember(
                self::TRAIT_SCOPE . 'getFrontChildrenCategories',
                [$categoryId, $langId, $shopId],
                function () use ($categoryId, $langId, $shopId) {
                    $sql = new \DbQuery();
                    $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
                    $sql->from('ever_blog_category', 'c');
                    $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $langId);
                    $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $shopId);
                    $sql->where('c.id_parent_category = ' . (int) $categoryId);
                    $sql->where('c.id_ever_category != ' . (int) $categoryId);
                    $sql->where('COALESCE(c.is_root_category, 0) = 0');
                    $sql->where('c.active = 1');
                    $sql->orderBy('cl.title ASC');

                    return $this->rowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
                },
                [BlogFrontCacheTags::category($categoryId)],
                function ($categories) {
                    return $this->extractEntityTags($categories, 'category', ['id', 'id_ever_category']);
                }
            );
        }
    }

    /**
     * @param array<string, int> $stats
     */
    private function warmTags(int $shopId, int $langId, int $pageLimit, int $perPage, array &$stats): void
    {
        foreach ($this->resolver->listTagIds($shopId) as $tagId) {
            ++$stats['tags'];

            $this->warmRemember(
                self::TRAIT_SCOPE . 'getFrontTag',
                [$tagId, $langId, $shopId],
                function () use ($tagId, $langId, $shopId) {
                    $sql = new \DbQuery();
                    $sql->select('t.*, t.id_ever_tag AS id, tl.title, tl.meta_title, tl.meta_description, tl.link_rewrite, tl.content, tl.bottom_content');
                    $sql->from('ever_blog_tag', 't');
                    $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $langId);
                    $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $shopId);
                    $sql->where('t.id_ever_tag = ' . (int) $tagId);

                    return $this->rowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
                },
                [BlogFrontCacheTags::tag($tagId)]
            );

            $totalPosts = (int) $this->warmRemember(
                self::TRAIT_SCOPE . 'countFrontPostsByTag',
                [$tagId, $langId, $shopId],
                function () use ($tagId, $langId, $shopId) {
                    $sql = $this->createTraitPostQuery($langId, $shopId, 'COUNT(DISTINCT p.id_ever_post)');
                    $sql->innerJoin('ever_blog_post_tag', 'pt', 'pt.id_ever_post = p.id_ever_post');
                    $sql->where('pt.id_ever_post_tag = ' . (int) $tagId);

                    return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                },
                [BlogFrontCacheTags::tag($tagId)]
            );

            $pageCount = max(1, min($pageLimit, (int) ceil(max(1, $totalPosts) / $perPage)));
            for ($page = 0; $page < $pageCount; ++$page) {
                $start = $page * $perPage;
                $this->warmRemember(
                    self::TRAIT_SCOPE . 'getFrontPostsByTag',
                    [$langId, $shopId, $tagId, $start, $perPage],
                    function () use ($langId, $shopId, $tagId, $start, $perPage) {
                        $sql = $this->createTraitPostQuery($langId, $shopId);
                        $sql->innerJoin('ever_blog_post_tag', 'pt', 'pt.id_ever_post = p.id_ever_post');
                        $sql->where('pt.id_ever_post_tag = ' . (int) $tagId);

                        return $this->executeTraitPostsQuery($sql, $shopId, $start, $perPage);
                    },
                    [BlogFrontCacheTags::tag($tagId)],
                    function ($posts) {
                        return $this->extractEntityTags($posts, 'post', ['id', 'id_ever_post']);
                    }
                );
            }
        }
    }

    /**
     * @param array<string, int> $stats
     */
    private function warmAuthors(int $shopId, int $langId, int $pageLimit, int $perPage, array &$stats): void
    {
        foreach ($this->resolver->listAuthorIds($shopId) as $authorId) {
            ++$stats['authors'];
            $excerptSelect = $this->authorExcerptColumnExists() ? 'al.excerpt' : '"" AS excerpt';

            $this->warmRemember(
                self::TRAIT_SCOPE . 'getFrontAuthor',
                [$authorId, $langId, $shopId],
                function () use ($authorId, $excerptSelect, $langId, $shopId) {
                    $sql = new \DbQuery();
                    $sql->select('a.*, a.id_ever_author AS id, al.meta_title, al.meta_description, al.link_rewrite, ' . $excerptSelect . ', al.content, al.bottom_content');
                    $sql->from('ever_blog_author', 'a');
                    $sql->innerJoin('ever_blog_author_lang', 'al', 'al.id_ever_author = a.id_ever_author AND al.id_lang = ' . (int) $langId);
                    $sql->innerJoin('ever_blog_author_shop', 'aus', 'aus.id_ever_author = a.id_ever_author AND aus.id_shop = ' . (int) $shopId);
                    $sql->where('a.id_ever_author = ' . (int) $authorId);

                    return $this->rowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
                },
                [BlogFrontCacheTags::author($authorId)]
            );

            $totalPosts = (int) $this->warmRemember(
                self::TRAIT_SCOPE . 'countFrontPostsByAuthor',
                [$authorId, $langId, $shopId],
                function () use ($authorId, $langId, $shopId) {
                    $sql = $this->createTraitPostQuery($langId, $shopId, 'COUNT(DISTINCT p.id_ever_post)');
                    $sql->where('p.id_author = ' . (int) $authorId);

                    return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                },
                [BlogFrontCacheTags::author($authorId)]
            );

            $pageCount = max(1, min($pageLimit, (int) ceil(max(1, $totalPosts) / $perPage)));
            for ($page = 0; $page < $pageCount; ++$page) {
                $start = $page * $perPage;
                $this->warmRemember(
                    self::TRAIT_SCOPE . 'getFrontPostsByAuthor',
                    [$langId, $shopId, $authorId, $start, $perPage],
                    function () use ($langId, $shopId, $authorId, $start, $perPage) {
                        $sql = $this->createTraitPostQuery($langId, $shopId);
                        $sql->where('p.id_author = ' . (int) $authorId);

                        return $this->executeTraitPostsQuery($sql, $shopId, $start, $perPage);
                    },
                    [BlogFrontCacheTags::author($authorId)],
                    function ($posts) {
                        return $this->extractEntityTags($posts, 'post', ['id', 'id_ever_post']);
                    }
                );
            }
        }
    }

    /**
     * @param array<string, int> $stats
     */
    private function warmPosts(int $shopId, int $langId, array &$stats): void
    {
        foreach ($this->resolver->listPublishedPostIds($shopId) as $postId) {
            ++$stats['posts'];
            $snapshot = $this->resolver->getPostSnapshot($postId);

            $this->warmRemember(
                self::TRAIT_SCOPE . 'getFrontPost',
                [$postId, $langId, $shopId],
                function () use ($postId, $langId, $shopId) {
                    $sql = $this->createTraitPostQuery($langId, $shopId);
                    $sql->where('p.id_ever_post = ' . (int) $postId);

                    return $this->rowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
                },
                [BlogFrontCacheTags::post($postId)]
            );

            $defaultCategoryId = (int) ($snapshot['default_category_id'] ?? 0);
            if ($defaultCategoryId > 0) {
                $this->warmRemember(
                    self::POST_SCOPE . 'getCategoryForFront',
                    [$defaultCategoryId, $langId, $shopId],
                    function () use ($defaultCategoryId, $langId, $shopId) {
                        $sql = new \DbQuery();
                        $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
                        $sql->from('ever_blog_category', 'c');
                        $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $langId);
                        $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $shopId);
                        $sql->where('c.id_ever_category = ' . (int) $defaultCategoryId);

                        return $this->rowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
                    },
                    [BlogFrontCacheTags::category($defaultCategoryId)]
                );

                $this->warmRemember(
                    self::POST_SCOPE . 'getPostsByCategoryForFront',
                    [$langId, $shopId, $defaultCategoryId, 0, 5],
                    function () use ($langId, $shopId, $defaultCategoryId) {
                        $sql = new \DbQuery();
                        $sql->select('p.id_ever_post, p.id_ever_post AS id, p.id_shop, p.id_author, p.id_author AS id_ever_author, p.id_default_category, p.post_status, p.date_add, p.date_upd, p.indexable, p.follow, p.sitemap, p.active, p.allowed_groups, p.post_categories, p.post_tags, p.post_products, p.psswd, p.starred, p.count, p.groups, pl.title AS title, pl.meta_title AS meta_title, pl.meta_description AS meta_description, pl.link_rewrite AS link_rewrite, pl.content AS content, pl.excerpt AS excerpt');
                        $sql->from('ever_blog_post', 'p');
                        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $langId);
                        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $shopId);
                        $sql->innerJoin('ever_blog_post_category', 'pc', 'pc.id_ever_post = p.id_ever_post');
                        $sql->where('pc.id_ever_post_category = ' . (int) $defaultCategoryId);
                        $sql->where('p.post_status = "published"');
                        $sql->orderBy('p.date_add DESC, p.id_ever_post DESC');
                        $sql->limit(5, 0);

                        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
                        foreach ($rows as &$row) {
                            $row['featured_thumb'] = $this->blogImageService->getBlogThumbUrl((int) $row['id_ever_post'], $shopId, 'post');
                            $row['featured_image'] = $this->blogImageService->getBlogImageUrl((int) $row['id_ever_post'], $shopId, 'post');
                            $row['cover'] = $row['featured_thumb'];
                        }

                        return $this->rowsToObjects($rows);
                    },
                    [BlogFrontCacheTags::category($defaultCategoryId)],
                    function ($posts) {
                        return $this->extractEntityTags($posts, 'post', ['id', 'id_ever_post']);
                    }
                );

                $this->warmRemember(
                    self::POST_SCOPE . 'getChildrenCategoriesForFront',
                    [$defaultCategoryId, $langId, $shopId],
                    function () use ($defaultCategoryId, $langId, $shopId) {
                        $sql = new \DbQuery();
                        $sql->select('c.*, c.id_ever_category AS id, cl.title, cl.meta_title, cl.meta_description, cl.link_rewrite, cl.content, cl.bottom_content');
                        $sql->from('ever_blog_category', 'c');
                        $sql->innerJoin('ever_blog_category_lang', 'cl', 'cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) $langId);
                        $sql->innerJoin('ever_blog_category_shop', 'cs', 'cs.id_ever_category = c.id_ever_category AND cs.id_shop = ' . (int) $shopId);
                        $sql->where('c.id_parent_category = ' . (int) $defaultCategoryId);
                        $sql->orderBy('cl.title ASC');

                        return $this->rowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
                    },
                    [BlogFrontCacheTags::category($defaultCategoryId)],
                    function ($categories) {
                        return $this->extractEntityTags($categories, 'category', ['id', 'id_ever_category']);
                    }
                );

                $this->warmRemember(
                    self::POST_SCOPE . 'categoryHasChildren',
                    [$defaultCategoryId],
                    function () use ($defaultCategoryId) {
                        $sql = new \DbQuery();
                        $sql->select('COUNT(*)');
                        $sql->from('ever_blog_category');
                        $sql->where('id_parent_category = ' . (int) $defaultCategoryId);

                        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
                    },
                    [BlogFrontCacheTags::category($defaultCategoryId)]
                );
            }

            $authorId = (int) ($snapshot['author_id'] ?? 0);
            if ($authorId > 0) {
                $excerptSelect = $this->authorExcerptColumnExists() ? 'al.excerpt' : '"" AS excerpt';
                $this->warmRemember(
                    self::POST_SCOPE . 'getAuthorForFront',
                    [$authorId, $langId, $shopId],
                    function () use ($authorId, $excerptSelect, $langId, $shopId) {
                        $sql = new \DbQuery();
                        $sql->select('a.*, a.id_ever_author AS id, al.meta_title, al.meta_description, al.link_rewrite, ' . $excerptSelect . ', al.content, al.bottom_content');
                        $sql->from('ever_blog_author', 'a');
                        $sql->innerJoin('ever_blog_author_lang', 'al', 'al.id_ever_author = a.id_ever_author AND al.id_lang = ' . (int) $langId);
                        $sql->innerJoin('ever_blog_author_shop', 'ass', 'ass.id_ever_author = a.id_ever_author AND ass.id_shop = ' . (int) $shopId);
                        $sql->where('a.id_ever_author = ' . (int) $authorId);

                        return $this->rowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
                    },
                    [BlogFrontCacheTags::author($authorId)]
                );
            }

            foreach ((array) ($snapshot['tag_ids'] ?? []) as $tagId) {
                if ($tagId <= 0) {
                    continue;
                }

                $this->warmRemember(
                    self::POST_SCOPE . 'getTagForFront',
                    [$tagId, $langId, $shopId],
                    function () use ($tagId, $langId, $shopId) {
                        $sql = new \DbQuery();
                        $sql->select('t.*, t.id_ever_tag AS id, tl.title, tl.meta_title, tl.meta_description, tl.link_rewrite, tl.content, tl.bottom_content');
                        $sql->from('ever_blog_tag', 't');
                        $sql->innerJoin('ever_blog_tag_lang', 'tl', 'tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) $langId);
                        $sql->innerJoin('ever_blog_tag_shop', 'ts', 'ts.id_ever_tag = t.id_ever_tag AND ts.id_shop = ' . (int) $shopId);
                        $sql->where('t.id_ever_tag = ' . (int) $tagId);

                        return $this->rowToObject(\Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql));
                    },
                    [BlogFrontCacheTags::tag($tagId)]
                );
            }

            $this->warmRemember(
                self::POST_SCOPE . 'getCommentsCount',
                [$postId, $langId],
                function () use ($postId, $langId) {
                    $sql = new \DbQuery();
                    $sql->select('COUNT(*)');
                    $sql->from('ever_blog_comments');
                    $sql->where('id_ever_post = ' . (int) $postId);
                    $sql->where('id_lang = ' . (int) $langId);
                    $sql->where('active = 1');

                    return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                },
                [BlogFrontCacheTags::postComments($postId)]
            );

            $this->warmRemember(
                self::POST_SCOPE . 'getCommentsByPost',
                [$postId, $langId],
                function () use ($postId, $langId) {
                    $sql = new \DbQuery();
                    $sql->select('*, id_ever_comment AS id');
                    $sql->from('ever_blog_comments');
                    $sql->where('id_ever_post = ' . (int) $postId);
                    $sql->where('id_lang = ' . (int) $langId);
                    $sql->where('active = 1');
                    $sql->orderBy('date_add ASC');

                    return $this->rowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
                },
                [BlogFrontCacheTags::postComments($postId)],
                function ($comments) {
                    return $this->extractEntityTags($comments, 'comment', ['id', 'id_ever_comment']);
                }
            );
        }
    }

    private function warmBlogRows(int $langId, int $shopId, int $start, int $limit, ?bool $starred): void
    {
        $sortBy = 'p.date_add';
        $sortWay = 'DESC';
        $tags = [BlogFrontCacheTags::BLOG_LISTING];
        if (null !== $starred) {
            $tags[] = BlogFrontCacheTags::BLOG_STARRED;
        }

        $this->warmRemember(
            self::BLOG_SCOPE . 'getPostRows',
            [$langId, $shopId, $start, $limit, $starred, $sortBy, $sortWay],
            function () use ($langId, $shopId, $start, $limit, $starred, $sortBy, $sortWay) {
                $sql = new \DbQuery();
                $sql->select('p.id_ever_post, p.id_ever_post AS id, p.id_default_category, p.id_author AS id_ever_author, p.post_status, p.date_add, p.date_upd, p.active, p.starred, p.count, pl.title AS title, pl.link_rewrite AS link_rewrite, pl.meta_title AS meta_title, pl.meta_description AS meta_description, pl.excerpt AS excerpt, pl.content AS content');
                $sql->from('ever_blog_post', 'p');
                $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $langId);
                $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $shopId);
                $sql->where('p.post_status = "published"');
                $sql->where('p.active = 1');
                if (null !== $starred) {
                    $sql->where('p.starred = ' . (int) $starred);
                }
                $sql->orderBy($sortBy . ' ' . $sortWay . ', p.id_ever_post DESC');
                $sql->limit((int) $limit, (int) $start);

                $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
                foreach ($rows as &$row) {
                    $row['url'] = (string) \Context::getContext()->link->getModuleLink(
                        'everpsblog',
                        'post',
                        [
                            'id_ever_post' => (int) $row['id_ever_post'],
                            'link_rewrite' => (string) $row['link_rewrite'],
                        ]
                    );
                    $row['featured_thumb'] = $this->blogImageService->getBlogThumbUrl((int) $row['id_ever_post'], $shopId, 'post');
                    $row['featured_image'] = $this->blogImageService->getBlogImageUrl((int) $row['id_ever_post'], $shopId, 'post');
                    $row['cover'] = $row['featured_thumb'];
                    $row['summary'] = $this->summarizePostRow($row);
                }

                return $rows;
            },
            $tags,
            function ($rows) {
                return $this->extractEntityTags($rows, 'post', ['id', 'id_ever_post']);
            }
        );
    }

    private function createTraitPostQuery(int $langId, int $shopId, ?string $select = null): \DbQuery
    {
        $sql = new \DbQuery();
        $sql->select($select ?: 'p.id_ever_post, p.id_ever_post AS id, p.id_shop, p.id_author, p.id_author AS id_ever_author, p.id_default_category, p.post_status, p.date_add, p.date_upd, p.indexable, p.follow, p.sitemap, p.active, p.allowed_groups, p.post_categories, p.post_tags, p.post_products, p.psswd, p.starred, p.count, p.groups, pl.title AS title, pl.meta_title AS meta_title, pl.meta_description AS meta_description, pl.link_rewrite AS link_rewrite, pl.content AS content, pl.excerpt AS excerpt');
        $sql->from('ever_blog_post', 'p');
        $sql->innerJoin('ever_blog_post_lang', 'pl', 'pl.id_ever_post = p.id_ever_post AND pl.id_lang = ' . (int) $langId);
        $sql->innerJoin('ever_blog_post_shop', 'ps', 'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $shopId);
        $sql->where('p.post_status = "published"');

        return $sql;
    }

    /**
     * @return array<int, object>
     */
    private function executeTraitPostsQuery(\DbQuery $sql, int $shopId, int $start, int $limit): array
    {
        $sql->orderBy('p.date_add DESC, p.id_ever_post DESC');
        $sql->limit($limit, $start);

        $posts = $this->rowsToObjects(\Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        foreach ($posts as $post) {
            $postId = (int) ($post->id ?? $post->id_ever_post ?? 0);
            if ($postId <= 0) {
                continue;
            }

            $post->url = (string) \Context::getContext()->link->getModuleLink(
                'everpsblog',
                'post',
                [
                    'id_ever_post' => $postId,
                    'link_rewrite' => (string) ($post->link_rewrite ?? ''),
                ]
            );
            $post->featured_image = $this->blogImageService->getBlogImageUrl($postId, $shopId, 'post');
            $post->featured_thumb = $this->blogImageService->getBlogThumbUrl($postId, $shopId, 'post');
            $post->cover = $post->featured_thumb;
            $post->summary = $this->summarizePostRow($post);
        }

        return $posts;
    }

    /**
     * @param mixed $value
     */
    private function summarizePostRow($value): string
    {
        $excerpt = $this->readValue($value, ['excerpt']);
        if ('' !== trim($excerpt)) {
            $summary = $excerpt;
        } else {
            $metaDescription = $this->readValue($value, ['meta_description']);
            if ('' !== trim($metaDescription)) {
                $summary = $metaDescription;
            } else {
                $summary = strip_tags($this->readValue($value, ['content']));
            }
        }

        $summary = trim($summary);

        return function_exists('mb_substr') ? (string) mb_substr($summary, 0, 300) : (string) substr($summary, 0, 300);
    }

    /**
     * @param array<int|string, mixed> $parts
     * @param callable(): mixed $resolver
     * @param string[] $tags
     * @param null|callable(mixed): array<int, string> $dynamicTagsResolver
     *
     * @return mixed
     */
    private function warmRemember(string $scope, array $parts, callable $resolver, array $tags = [], ?callable $dynamicTagsResolver = null)
    {
        return $this->cache->remember($scope, $parts, $resolver, $tags, $dynamicTagsResolver);
    }

    /**
     * @param mixed $row
     *
     * @return object
     */
    private function rowToObject($row)
    {
        return is_array($row) ? (object) $row : (object) [];
    }

    /**
     * @param mixed $rows
     *
     * @return array<int, object>
     */
    private function rowsToObjects($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        return array_map(function ($row) {
            return is_array($row) ? (object) $row : (object) [];
        }, $rows);
    }

    /**
     * @param mixed $items
     * @param string[] $idFields
     *
     * @return string[]
     */
    private function extractEntityTags($items, string $entityType, array $idFields): array
    {
        if (!is_iterable($items)) {
            return [];
        }

        $tags = [];
        foreach ($items as $item) {
            $entityId = $this->readId($item, $idFields);
            if ($entityId <= 0) {
                continue;
            }

            switch ($entityType) {
                case 'post':
                    $tags[] = BlogFrontCacheTags::post($entityId);
                    break;
                case 'category':
                    $tags[] = BlogFrontCacheTags::category($entityId);
                    break;
                case 'tag':
                    $tags[] = BlogFrontCacheTags::tag($entityId);
                    break;
                case 'author':
                    $tags[] = BlogFrontCacheTags::author($entityId);
                    break;
                case 'comment':
                    $tags[] = BlogFrontCacheTags::comment($entityId);
                    break;
            }
        }

        return array_values(array_unique(array_filter($tags)));
    }

    /**
     * @param mixed $item
     * @param string[] $idFields
     */
    private function readId($item, array $idFields): int
    {
        foreach ($idFields as $idField) {
            if (is_array($item) && isset($item[$idField])) {
                return (int) $item[$idField];
            }

            if (is_object($item) && isset($item->{$idField})) {
                return (int) $item->{$idField};
            }
        }

        return 0;
    }

    /**
     * @param mixed $item
     * @param string[] $fields
     */
    private function readValue($item, array $fields): string
    {
        foreach ($fields as $field) {
            if (is_array($item) && isset($item[$field])) {
                return (string) $item[$field];
            }

            if (is_object($item) && isset($item->{$field})) {
                return (string) $item->{$field};
            }
        }

        return '';
    }

    private function authorExcerptColumnExists(): bool
    {
        static $exists = null;

        if (null === $exists) {
            $exists = (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                'DESCRIBE `' . _DB_PREFIX_ . 'ever_blog_author_lang` `excerpt`'
            );
        }

        return (bool) $exists;
    }
}
