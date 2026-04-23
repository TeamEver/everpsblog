<?php

namespace PrestaShop\Module\Everpsblog\Service;

use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

class WordPressRestImporter
{
    /** @var BlogImageService */
    private $blogImageService;

    /** @var BlogSitemapService */
    private $blogSitemapService;

    /** @var BlogRedirectService */
    private $blogRedirectService;

    /** @var BlogInstallService */
    private $blogInstallService;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    /** @var array<int, int> */
    private $categoryCache = [];

    /** @var array<int, int> */
    private $tagCache = [];

    /** @var array<int, int> */
    private $authorCache = [];

    /** @var int */
    private $redirectsSaved = 0;

    public function __construct(
        BlogImageService $blogImageService,
        BlogSitemapService $blogSitemapService,
        BlogRedirectService $blogRedirectService,
        BlogInstallService $blogInstallService,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null
    ) {
        $this->blogImageService = $blogImageService;
        $this->blogSitemapService = $blogSitemapService;
        $this->blogRedirectService = $blogRedirectService;
        $this->blogInstallService = $blogInstallService;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function import(string $siteUrl, string $username, string $password, int $shopId, int $defaultLangId): array
    {
        @set_time_limit(0);

        $apiBase = $this->normalizeApiBase($siteUrl);
        if ('' === $apiBase) {
            throw new \InvalidArgumentException('WordPress API URL is required.');
        }

        $this->categoryCache = [];
        $this->tagCache = [];
        $this->authorCache = [];
        $this->redirectsSaved = 0;

        $stats = [
            'posts_created' => 0,
            'posts_updated' => 0,
            'categories' => 0,
            'tags' => 0,
            'authors' => 0,
            'images' => 0,
            'redirects' => 0,
            'skipped' => 0,
        ];

        $page = 1;
        $status = ('' !== $username && '' !== $password) ? 'any' : 'publish';
        do {
            $endpoint = $apiBase . '/posts?per_page=100&page=' . (int) $page . '&_embed=1&status=' . rawurlencode($status);
            $posts = $this->request($endpoint, $username, $password);
            if (null === $posts && 1 === $page && 'any' === $status) {
                $status = 'publish';
                $posts = $this->request($apiBase . '/posts?per_page=100&page=1&_embed=1&status=publish', $username, $password);
            }

            if (!is_array($posts) || empty($posts)) {
                break;
            }

            foreach ($posts as $postData) {
                if (!is_array($postData)) {
                    $stats['skipped']++;
                    continue;
                }

                $result = $this->importPost($postData, $apiBase, $username, $password, $shopId, $defaultLangId);
                foreach ($result as $key => $value) {
                    if (isset($stats[$key])) {
                        $stats[$key] += (int) $value;
                    }
                }
            }

            $page++;
        } while (true);

        $this->blogImageService->clearCache();
        $this->cacheInvalidator->invalidateAll();
        $this->blogSitemapService->refreshForShop($shopId);
        $stats['redirects'] = $this->redirectsSaved;

        return $stats;
    }

    private function importPost(array $data, string $apiBase, string $username, string $password, int $shopId, int $defaultLangId): array
    {
        $stats = [
            'posts_created' => 0,
            'posts_updated' => 0,
            'categories' => 0,
            'tags' => 0,
            'authors' => 0,
            'images' => 0,
            'redirects' => 0,
            'skipped' => 0,
        ];

        $title = $this->decodeRendered($data['title']['rendered'] ?? ($data['title'] ?? ''));
        $slug = \Tools::str2url((string) ($data['slug'] ?? $title));
        if ('' === $slug || '' === $title) {
            $stats['skipped']++;

            return $stats;
        }

        $content = $this->cleanWordPressContent($this->decodeRendered($data['content']['rendered'] ?? ''));
        $content = $this->localizeContentImages($content);
        $excerpt = $this->buildExcerpt($data, $content);
        $dateAdd = $this->formatWordPressDate((string) ($data['date'] ?? 'now'));
        $dateUpd = $this->formatWordPressDate((string) ($data['modified'] ?? $dateAdd));
        $status = $this->resolvePostStatus((string) ($data['status'] ?? 'publish'));

        $categoryIds = [];
        foreach ((array) ($data['categories'] ?? []) as $wpCategoryId) {
            $categoryId = $this->ensureCategory((int) $wpCategoryId, $apiBase, $username, $password, $shopId, $defaultLangId);
            if ($categoryId > 0) {
                $categoryIds[] = $categoryId;
            }
        }
        $categoryIds = array_values(array_unique($categoryIds));
        $stats['categories'] += count($categoryIds);

        $tagIds = [];
        foreach ((array) ($data['tags'] ?? []) as $wpTagId) {
            $tagId = $this->ensureTag((int) $wpTagId, $apiBase, $username, $password, $shopId, $defaultLangId);
            if ($tagId > 0) {
                $tagIds[] = $tagId;
            }
        }
        $tagIds = array_values(array_unique($tagIds));
        $stats['tags'] += count($tagIds);

        $authorId = $this->ensureAuthorFromPostData($data, $apiBase, $username, $password, $shopId, $defaultLangId);
        if ($authorId > 0) {
            $stats['authors']++;
        }

        $defaultCategoryId = $categoryIds[0] ?? $this->getFallbackCategoryId($shopId);
        if ($defaultCategoryId <= 0) {
            $stats['skipped']++;

            return $stats;
        }

        $postId = $this->findPostBySlug($slug, $shopId);
        $db = \Db::getInstance();
        $postPayload = [
            'id_shop' => $shopId,
            'id_author' => $authorId,
            'id_default_category' => $defaultCategoryId,
            'post_status' => pSQL($status),
            'date_add' => pSQL($dateAdd),
            'date_upd' => pSQL($dateUpd),
            'indexable' => 1,
            'follow' => 1,
            'sitemap' => 1,
            'active' => 'published' === $status ? 1 : 0,
            'allowed_groups' => null,
            'post_categories' => json_encode($categoryIds),
            'post_tags' => json_encode($tagIds),
            'post_products' => null,
            'psswd' => null,
            'starred' => 0,
            'groups' => null,
        ];

        if ($postId > 0) {
            $db->update('ever_blog_post', $postPayload, 'id_ever_post = ' . (int) $postId);
            $stats['posts_updated']++;
        } else {
            $db->insert('ever_blog_post', $postPayload);
            $postId = (int) $db->Insert_ID();
            $stats['posts_created']++;
        }

        $this->replaceShopRelation('ever_blog_post_shop', 'id_ever_post', $postId, $shopId);
        $this->replacePostTranslations($postId, $title, $content, $excerpt, $slug);
        $this->replacePostRelations($postId, $categoryIds, $tagIds);
        $this->saveRedirectFromWordPressData(
            $data,
            'post',
            [
                'id_ever_post' => $postId,
                'link_rewrite' => $slug,
            ],
            $shopId,
            $defaultLangId,
            'post',
            $postId
        );

        $featuredImageUrl = $this->resolveFeaturedImageUrl($data, $apiBase, $username, $password);
        if ('' !== $featuredImageUrl && $this->importFeaturedImage($featuredImageUrl, $postId, $shopId)) {
            $stats['images']++;
        }

        return $stats;
    }

    private function ensureCategory(int $wpCategoryId, string $apiBase, string $username, string $password, int $shopId, int $defaultLangId): int
    {
        if ($wpCategoryId <= 0) {
            return 0;
        }
        if (isset($this->categoryCache[$wpCategoryId])) {
            return $this->categoryCache[$wpCategoryId];
        }

        $data = $this->request($apiBase . '/categories/' . $wpCategoryId, $username, $password);
        if (!is_array($data) || empty($data['slug'])) {
            return 0;
        }

        $slug = \Tools::str2url((string) $data['slug']);
        $existingId = $this->findTaxonomyBySlug('ever_blog_category', 'ever_blog_category_lang', 'id_ever_category', $slug, $shopId);
        if ($existingId > 0) {
            $this->categoryCache[$wpCategoryId] = $existingId;
            $this->saveRedirectFromWordPressData(
                $data,
                'category',
                [
                    'id_ever_category' => $existingId,
                    'link_rewrite' => $slug,
                ],
                $shopId,
                $defaultLangId,
                'category',
                $existingId
            );

            return $existingId;
        }

        $parentId = $this->getRootCategoryId($shopId);
        if (!empty($data['parent'])) {
            $importedParentId = $this->ensureCategory((int) $data['parent'], $apiBase, $username, $password, $shopId, $defaultLangId);
            if ($importedParentId > 0) {
                $parentId = $importedParentId;
            }
        }

        $now = date('Y-m-d H:i:s');
        \Db::getInstance()->insert('ever_blog_category', [
            'id_parent_category' => $parentId,
            'id_shop' => $shopId,
            'date_add' => pSQL($now),
            'date_upd' => pSQL($now),
            'indexable' => 1,
            'follow' => 1,
            'sitemap' => 1,
            'active' => $this->configBool('EVERBLOG_ENABLE_CATS', true) ? 1 : 0,
            'category_products' => null,
            'allowed_groups' => null,
            'is_root_category' => 0,
            'count' => 0,
            'groups' => null,
        ]);
        $categoryId = (int) \Db::getInstance()->Insert_ID();
        $title = $this->decodeRendered($data['name'] ?? $slug);
        $description = $this->cleanWordPressContent($this->decodeRendered($data['description'] ?? ''));
        $this->replaceShopRelation('ever_blog_category_shop', 'id_ever_category', $categoryId, $shopId);
        $this->replaceTaxonomyTranslations('ever_blog_category_lang', 'id_ever_category', $categoryId, $title, $slug, $description);

        $this->categoryCache[$wpCategoryId] = $categoryId;
        $this->saveRedirectFromWordPressData(
            $data,
            'category',
            [
                'id_ever_category' => $categoryId,
                'link_rewrite' => $slug,
            ],
            $shopId,
            $defaultLangId,
            'category',
            $categoryId
        );

        return $categoryId;
    }

    private function ensureTag(int $wpTagId, string $apiBase, string $username, string $password, int $shopId, int $defaultLangId): int
    {
        if ($wpTagId <= 0) {
            return 0;
        }
        if (isset($this->tagCache[$wpTagId])) {
            return $this->tagCache[$wpTagId];
        }

        $data = $this->request($apiBase . '/tags/' . $wpTagId, $username, $password);
        if (!is_array($data) || empty($data['slug'])) {
            return 0;
        }

        $slug = \Tools::str2url((string) $data['slug']);
        $existingId = $this->findTaxonomyBySlug('ever_blog_tag', 'ever_blog_tag_lang', 'id_ever_tag', $slug, $shopId);
        if ($existingId > 0) {
            $this->tagCache[$wpTagId] = $existingId;
            $this->saveRedirectFromWordPressData(
                $data,
                'tag',
                [
                    'id_ever_tag' => $existingId,
                    'link_rewrite' => $slug,
                ],
                $shopId,
                $defaultLangId,
                'tag',
                $existingId
            );

            return $existingId;
        }

        $now = date('Y-m-d H:i:s');
        \Db::getInstance()->insert('ever_blog_tag', [
            'id_shop' => $shopId,
            'date_add' => pSQL($now),
            'date_upd' => pSQL($now),
            'indexable' => 1,
            'follow' => 1,
            'sitemap' => 1,
            'active' => $this->configBool('EVERBLOG_ENABLE_TAGS', true) ? 1 : 0,
            'allowed_groups' => null,
            'tag_products' => null,
            'count' => 0,
        ]);
        $tagId = (int) \Db::getInstance()->Insert_ID();
        $title = $this->decodeRendered($data['name'] ?? $slug);
        $description = $this->cleanWordPressContent($this->decodeRendered($data['description'] ?? ''));
        $this->replaceShopRelation('ever_blog_tag_shop', 'id_ever_tag', $tagId, $shopId);
        $this->replaceTaxonomyTranslations('ever_blog_tag_lang', 'id_ever_tag', $tagId, $title, $slug, $description);

        $this->tagCache[$wpTagId] = $tagId;
        $this->saveRedirectFromWordPressData(
            $data,
            'tag',
            [
                'id_ever_tag' => $tagId,
                'link_rewrite' => $slug,
            ],
            $shopId,
            $defaultLangId,
            'tag',
            $tagId
        );

        return $tagId;
    }

    private function ensureAuthorFromPostData(array $postData, string $apiBase, string $username, string $password, int $shopId, int $defaultLangId): int
    {
        $wpAuthorId = (int) ($postData['author'] ?? 0);
        if ($wpAuthorId <= 0) {
            return 0;
        }
        if (isset($this->authorCache[$wpAuthorId])) {
            return $this->authorCache[$wpAuthorId];
        }

        $data = $postData['_embedded']['author'][0] ?? null;
        if (!is_array($data)) {
            $data = $this->request($apiBase . '/users/' . $wpAuthorId, $username, $password);
        }
        if (!is_array($data)) {
            return 0;
        }

        $slug = \Tools::str2url((string) ($data['slug'] ?? ($data['name'] ?? 'author-' . $wpAuthorId)));
        $existingId = (int) \Db::getInstance()->getValue(
            'SELECT id_ever_author FROM `' . _DB_PREFIX_ . 'ever_blog_author`
             WHERE nickhandle = "' . pSQL($slug) . '" AND id_shop = ' . (int) $shopId
        );
        if ($existingId > 0) {
            $this->authorCache[$wpAuthorId] = $existingId;
            $this->saveRedirectFromWordPressData(
                $data,
                'author',
                [
                    'id_ever_author' => $existingId,
                    'link_rewrite' => $slug,
                ],
                $shopId,
                $defaultLangId,
                'author',
                $existingId
            );

            return $existingId;
        }

        $now = date('Y-m-d H:i:s');
        \Db::getInstance()->insert('ever_blog_author', [
            'id_employee' => 0,
            'id_shop' => $shopId,
            'nickhandle' => pSQL($slug),
            'twitter' => null,
            'facebook' => null,
            'linkedin' => null,
            'date_add' => pSQL($now),
            'date_upd' => pSQL($now),
            'indexable' => 1,
            'follow' => 1,
            'sitemap' => 1,
            'allowed_groups' => null,
            'author_products' => null,
            'active' => $this->configBool('EVERBLOG_ENABLE_AUTHORS', true) ? 1 : 0,
            'count' => 0,
        ]);
        $authorId = (int) \Db::getInstance()->Insert_ID();
        $name = $this->decodeRendered($data['name'] ?? $slug);
        $description = $this->cleanWordPressContent($this->decodeRendered($data['description'] ?? ''));
        $this->replaceShopRelation('ever_blog_author_shop', 'id_ever_author', $authorId, $shopId);
        $this->replaceAuthorTranslations($authorId, $name, $slug, $description);

        $this->authorCache[$wpAuthorId] = $authorId;
        $this->saveRedirectFromWordPressData(
            $data,
            'author',
            [
                'id_ever_author' => $authorId,
                'link_rewrite' => $slug,
            ],
            $shopId,
            $defaultLangId,
            'author',
            $authorId
        );

        return $authorId;
    }

    private function saveRedirectFromWordPressData(
        array $data,
        string $controller,
        array $params,
        int $shopId,
        int $defaultLangId,
        string $entityType,
        int $entityId
    ): void {
        $sourceUrl = (string) ($data['link'] ?? '');
        if ('' === trim($sourceUrl)) {
            return;
        }

        $targetUrl = $this->buildModuleLink($controller, $params, $shopId, $defaultLangId);
        if ('' === $targetUrl) {
            return;
        }

        if ($this->blogRedirectService->saveRedirect($sourceUrl, $targetUrl, $shopId, $entityType, $entityId)) {
            ++$this->redirectsSaved;
        }
    }

    private function buildModuleLink(string $controller, array $params, int $shopId, int $defaultLangId): string
    {
        $context = \Context::getContext();
        if (!isset($context->link) || !$context->link instanceof \Link) {
            $context->link = new \Link();
        }

        try {
            return (string) $context->link->getModuleLink(
                'everpsblog',
                $controller,
                $params,
                true,
                $defaultLangId,
                $shopId
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('EverPsBlog WordPress import redirect link failed: ' . $exception->getMessage(), 2);

            return '';
        }
    }

    private function replacePostTranslations(int $postId, string $title, string $content, string $excerpt, string $slug): void
    {
        \Db::getInstance()->delete('ever_blog_post_lang', 'id_ever_post = ' . (int) $postId);
        foreach (\Language::getLanguages(false) as $language) {
            \Db::getInstance()->insert('ever_blog_post_lang', [
                'id_ever_post' => $postId,
                'id_lang' => (int) $language['id_lang'],
                'title' => pSQL($title),
                'meta_title' => pSQL($title),
                'meta_description' => pSQL(\Tools::substr(strip_tags($content), 0, 160)),
                'link_rewrite' => pSQL($slug),
                'content' => pSQL($content, true),
                'excerpt' => pSQL(\Tools::substr(strip_tags($excerpt), 0, 255)),
            ]);
        }
    }

    private function replaceTaxonomyTranslations(string $table, string $primary, int $id, string $title, string $slug, string $content): void
    {
        \Db::getInstance()->delete($table, bqSQL($primary) . ' = ' . (int) $id);
        foreach (\Language::getLanguages(false) as $language) {
            \Db::getInstance()->insert($table, [
                $primary => $id,
                'id_lang' => (int) $language['id_lang'],
                'title' => pSQL($title),
                'meta_title' => pSQL($title),
                'meta_description' => pSQL(\Tools::substr(strip_tags($content), 0, 160)),
                'link_rewrite' => pSQL($slug),
                'content' => pSQL($content, true),
                'bottom_content' => '',
            ]);
        }
    }

    private function replaceAuthorTranslations(int $authorId, string $name, string $slug, string $content): void
    {
        \Db::getInstance()->delete('ever_blog_author_lang', 'id_ever_author = ' . (int) $authorId);
        foreach (\Language::getLanguages(false) as $language) {
            \Db::getInstance()->insert('ever_blog_author_lang', [
                'id_ever_author' => $authorId,
                'id_lang' => (int) $language['id_lang'],
                'meta_title' => pSQL($name),
                'meta_description' => pSQL(\Tools::substr(strip_tags($content), 0, 160)),
                'link_rewrite' => pSQL($slug),
                'content' => pSQL($content, true),
                'bottom_content' => '',
            ]);
        }
    }

    private function replacePostRelations(int $postId, array $categoryIds, array $tagIds): void
    {
        \Db::getInstance()->delete('ever_blog_post_category', 'id_ever_post = ' . (int) $postId);
        foreach ($categoryIds as $categoryId) {
            \Db::getInstance()->insert('ever_blog_post_category', [
                'id_ever_post' => $postId,
                'id_ever_post_category' => (int) $categoryId,
            ]);
        }

        \Db::getInstance()->delete('ever_blog_post_tag', 'id_ever_post = ' . (int) $postId);
        foreach ($tagIds as $tagId) {
            \Db::getInstance()->insert('ever_blog_post_tag', [
                'id_ever_post' => $postId,
                'id_ever_post_tag' => (int) $tagId,
            ]);
        }
    }

    private function replaceShopRelation(string $table, string $primary, int $id, int $shopId): void
    {
        \Db::getInstance()->delete($table, bqSQL($primary) . ' = ' . (int) $id . ' AND id_shop = ' . (int) $shopId);
        \Db::getInstance()->insert($table, [
            $primary => $id,
            'id_shop' => $shopId,
        ]);
    }

    private function findPostBySlug(string $slug, int $shopId): int
    {
        return (int) \Db::getInstance()->getValue(
            'SELECT p.id_ever_post
             FROM `' . _DB_PREFIX_ . 'ever_blog_post` p
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_post_lang` pl ON pl.id_ever_post = p.id_ever_post
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_post_shop` ps ON ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $shopId . '
             WHERE pl.link_rewrite = "' . pSQL($slug) . '"
             ORDER BY p.id_ever_post DESC'
        );
    }

    private function findTaxonomyBySlug(string $table, string $langTable, string $primary, string $slug, int $shopId): int
    {
        $shopTable = str_replace('_lang', '_shop', $langTable);

        return (int) \Db::getInstance()->getValue(
            'SELECT t.`' . bqSQL($primary) . '`
             FROM `' . _DB_PREFIX_ . bqSQL($table) . '` t
             INNER JOIN `' . _DB_PREFIX_ . bqSQL($langTable) . '` tl ON tl.`' . bqSQL($primary) . '` = t.`' . bqSQL($primary) . '`
             INNER JOIN `' . _DB_PREFIX_ . bqSQL($shopTable) . '` ts ON ts.`' . bqSQL($primary) . '` = t.`' . bqSQL($primary) . '` AND ts.id_shop = ' . (int) $shopId . '
             WHERE tl.link_rewrite = "' . pSQL($slug) . '"
             ORDER BY t.`' . bqSQL($primary) . '` DESC'
        );
    }

    private function getRootCategoryId(int $shopId): int
    {
        return (int) \Db::getInstance()->getValue(
            'SELECT id_ever_category FROM `' . _DB_PREFIX_ . 'ever_blog_category`
             WHERE is_root_category = 1 AND id_shop = ' . (int) $shopId
        );
    }

    private function getFallbackCategoryId(int $shopId): int
    {
        $rootId = $this->blogInstallService->ensureRootCategory($shopId);
        $unclassedId = $this->blogInstallService->ensureUnclassedCategory($shopId, $rootId);
        if ($unclassedId > 0) {
            return $unclassedId;
        }

        if ($rootId > 0) {
            return $rootId;
        }

        return (int) \Db::getInstance()->getValue(
            'SELECT c.id_ever_category
             FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_shop` cs
                ON cs.id_ever_category = c.id_ever_category
             WHERE COALESCE(c.is_root_category, 0) = 0
                AND (c.id_shop = ' . (int) $shopId . ' OR cs.id_shop = ' . (int) $shopId . ')
             ORDER BY c.id_ever_category ASC'
        );
    }

    private function resolveFeaturedImageUrl(array $postData, string $apiBase, string $username, string $password): string
    {
        $embedded = $postData['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';
        if (is_string($embedded) && '' !== $embedded) {
            return $embedded;
        }

        $mediaId = (int) ($postData['featured_media'] ?? 0);
        if ($mediaId <= 0) {
            return '';
        }

        $media = $this->request($apiBase . '/media/' . $mediaId, $username, $password);

        return is_array($media) && !empty($media['source_url']) ? (string) $media['source_url'] : '';
    }

    private function importFeaturedImage(string $imageUrl, int $postId, int $shopId): bool
    {
        $content = $this->download($imageUrl, '', '');
        if (null === $content || '' === $content) {
            return false;
        }

        $path = (string) parse_url($imageUrl, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $extension = 'jpg';
        }
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }

        $targetDir = _PS_IMG_DIR_ . 'post/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $target = $targetDir . (int) $postId . '.' . $extension;
        if (false === file_put_contents($target, $content)) {
            return false;
        }

        $relativePath = 'img/post/' . (int) $postId . '.' . $extension;
        $imageId = (int) \Db::getInstance()->getValue(
            'SELECT id_ever_image FROM `' . _DB_PREFIX_ . 'ever_blog_image`
             WHERE id_element = ' . (int) $postId . '
             AND id_shop = ' . (int) $shopId . '
             AND image_type = "post"'
        );

        if ($imageId > 0) {
            \Db::getInstance()->update('ever_blog_image', [
                'image_link' => pSQL($relativePath),
            ], 'id_ever_image = ' . (int) $imageId);
        } else {
            \Db::getInstance()->insert('ever_blog_image', [
                'id_element' => $postId,
                'id_shop' => $shopId,
                'image_type' => 'post',
                'image_link' => pSQL($relativePath),
            ]);
            $imageId = (int) \Db::getInstance()->Insert_ID();
        }

        if ($imageId > 0) {
            $this->replaceShopRelation('ever_blog_image_shop', 'id_ever_image', $imageId, $shopId);
        }

        return true;
    }

    private function request(string $url, string $username, string $password)
    {
        $body = $this->download($url, $username, $password);
        if (null === $body || '' === $body) {
            return null;
        }

        $decoded = json_decode($body, true);

        return JSON_ERROR_NONE === json_last_error() ? $decoded : null;
    }

    private function download(string $url, string $username, string $password): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 45);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_USERAGENT, 'EverPsBlog WordPress importer');
            $headers = ['Accept: application/json, image/*;q=0.9, */*;q=0.8'];
            if ('' !== $username && '' !== $password) {
                $headers[] = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if (false === $body || $status >= 400) {
                if ($error) {
                    \PrestaShopLogger::addLog('EverPsBlog WordPress import request failed: ' . $error, 2);
                }

                return null;
            }

            return (string) $body;
        }

        $headers = "Accept: application/json, image/*;q=0.9, */*;q=0.8\r\n";
        if ('' !== $username && '' !== $password) {
            $headers .= 'Authorization: Basic ' . base64_encode($username . ':' . $password) . "\r\n";
        }
        $context = stream_context_create([
            'http' => [
                'timeout' => 45,
                'header' => $headers,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);

        return false === $body ? null : (string) $body;
    }

    private function normalizeApiBase(string $siteUrl): string
    {
        $siteUrl = trim($siteUrl);
        if ('' === $siteUrl) {
            return '';
        }

        $siteUrl = rtrim($siteUrl, '/');
        if (preg_match('#/wp-json/wp/v2$#', $siteUrl)) {
            return $siteUrl;
        }
        if (preg_match('#/wp-json$#', $siteUrl)) {
            return $siteUrl . '/wp/v2';
        }

        return $siteUrl . '/wp-json/wp/v2';
    }

    private function decodeRendered($value): string
    {
        if (is_array($value)) {
            $value = (string) ($value['rendered'] ?? '');
        }

        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function cleanWordPressContent(string $content): string
    {
        $content = preg_replace('/\[[^\]]+\]/', '', $content);
        $content = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', (string) $content);
        $content = preg_replace_callback('/<img([^>]+)>/i', function ($matches) {
            $tag = $matches[0];
            if (false !== stripos($tag, 'class=')) {
                return preg_replace('/class=["\']([^"\']*)["\']/i', 'class="$1 img-fluid"', $tag);
            }

            return str_replace('<img', '<img class="img-fluid"', $tag);
        }, (string) $content);

        return trim((string) $content);
    }

    private function localizeContentImages(string $content): string
    {
        return preg_replace_callback('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function ($matches) {
            $src = (string) $matches[1];
            if (!preg_match('#^https?://#i', $src)) {
                return $matches[0];
            }

            $localUrl = $this->storeContentImage($src);
            if ('' === $localUrl) {
                return $matches[0];
            }

            return str_replace($src, $localUrl, $matches[0]);
        }, $content);
    }

    private function storeContentImage(string $url): string
    {
        $content = $this->download($url, '', '');
        if (null === $content || '' === $content) {
            return '';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $filename = basename($path);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        if (!$filename || false === strpos($filename, '.')) {
            $filename = md5($url) . '.jpg';
        }

        $targetDir = _PS_IMG_DIR_ . 'cms/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $target = $targetDir . $filename;
        if (!file_exists($target) && false === file_put_contents($target, $content)) {
            return '';
        }

        return _PS_IMG_ . 'cms/' . $filename;
    }

    private function buildExcerpt(array $data, string $content): string
    {
        $excerpt = $this->decodeRendered($data['excerpt']['rendered'] ?? '');
        $excerpt = trim(strip_tags($excerpt));
        if ('' === $excerpt) {
            $excerpt = trim(strip_tags($content));
        }

        return \Tools::substr($excerpt, 0, 255);
    }

    private function formatWordPressDate(string $date): string
    {
        $timestamp = strtotime($date);
        if (false === $timestamp) {
            $timestamp = time();
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function resolvePostStatus(string $wpStatus): string
    {
        $configured = (string) \Configuration::get('EVERBLOG_IMPORT_POST_STATE');
        if (in_array($configured, ['published', 'draft', 'protected', 'planned'], true)) {
            return $configured;
        }

        return 'publish' === $wpStatus ? 'published' : 'draft';
    }

    private function configBool(string $key, bool $default): bool
    {
        $value = \Configuration::get($key);
        if (false === $value || null === $value || '' === $value) {
            return $default;
        }

        return (bool) $value;
    }
}
