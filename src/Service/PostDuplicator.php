<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class PostDuplicator
{
    /** @var BlogImageService */
    private $blogImageService;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(BlogImageService $blogImageService, ?BlogFrontCacheInvalidator $cacheInvalidator = null)
    {
        $this->blogImageService = $blogImageService;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function duplicate(int $sourcePostId, int $shopId): int
    {
        $sourcePostId = (int) $sourcePostId;
        $shopId = (int) $shopId;
        if ($sourcePostId <= 0 || $shopId <= 0) {
            throw new \InvalidArgumentException('Invalid post or shop identifier.');
        }

        $source = $this->getSourcePost($sourcePostId, $shopId);
        if (!$source) {
            throw new \RuntimeException(sprintf('Post #%d was not found for the current shop.', $sourcePostId));
        }

        $newPostId = $this->insertPostCopy($source, $shopId);
        $this->insertShopRelation($newPostId, $shopId);
        $this->duplicateTranslations($sourcePostId, $newPostId);
        $this->duplicateRelations($sourcePostId, $newPostId);
        $this->ensureDefaultCategoryRelation($newPostId, (int) ($source['id_default_category'] ?? 0));
        $this->duplicatePostImage($sourcePostId, $newPostId, $shopId, 'post');
        $this->duplicatePostImage($sourcePostId, $newPostId, $shopId, 'post_banner');
        $this->cacheInvalidator->invalidatePostMutation($newPostId);

        return $newPostId;
    }

    private function getSourcePost(int $sourcePostId, int $shopId): ?array
    {
        $sql = new \DbQuery();
        $sql->select('p.*');
        $sql->from('ever_blog_post', 'p');
        $sql->leftJoin(
            'ever_blog_post_shop',
            'ps',
            'ps.id_ever_post = p.id_ever_post AND ps.id_shop = ' . (int) $shopId
        );
        $sql->where('p.id_ever_post = ' . (int) $sourcePostId);
        $sql->where('(p.id_shop = ' . (int) $shopId . ' OR ps.id_shop = ' . (int) $shopId . ')');

        $row = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return is_array($row) ? $row : null;
    }

    private function insertPostCopy(array $source, int $shopId): int
    {
        $now = date('Y-m-d H:i:s');
        $payload = [
            'id_shop' => $shopId,
            'id_author' => (int) ($source['id_author'] ?? 0),
            'id_default_category' => (int) ($source['id_default_category'] ?? 0),
            'post_status' => \pSQL('draft'),
            'date_add' => \pSQL($now),
            'date_upd' => \pSQL($now),
            'indexable' => (int) ($source['indexable'] ?? 0),
            'follow' => (int) ($source['follow'] ?? 0),
            'sitemap' => (int) ($source['sitemap'] ?? 1),
            'active' => (int) ($source['active'] ?? 1),
            'allowed_groups' => $this->nullableString($source['allowed_groups'] ?? null),
            'post_categories' => $this->nullableString($source['post_categories'] ?? null),
            'post_tags' => $this->nullableString($source['post_tags'] ?? null),
            'post_products' => $this->nullableString($source['post_products'] ?? null),
            'psswd' => $this->nullableString($source['psswd'] ?? null),
            'starred' => (int) ($source['starred'] ?? 0),
            'count' => 0,
            'groups' => $this->nullableString($source['groups'] ?? null),
        ];

        if (!\Db::getInstance()->insert('ever_blog_post', $payload, true)) {
            throw new \RuntimeException('Unable to create the duplicated post.');
        }

        $newPostId = (int) \Db::getInstance()->Insert_ID();
        if ($newPostId <= 0) {
            throw new \RuntimeException('Unable to retrieve the duplicated post identifier.');
        }

        return $newPostId;
    }

    private function insertShopRelation(int $postId, int $shopId): void
    {
        \Db::getInstance()->execute(
            'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ever_blog_post_shop` (`id_ever_post`, `id_shop`) VALUES ('
            . (int) $postId . ', ' . (int) $shopId . ')'
        );
    }

    private function duplicateTranslations(int $sourcePostId, int $newPostId): void
    {
        $translations = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'ever_blog_post_lang`
             WHERE id_ever_post = ' . (int) $sourcePostId
        ) ?: [];

        foreach ($translations as $translation) {
            $title = trim((string) ($translation['title'] ?? ''));
            if ('' === $title) {
                $title = 'Article #' . (int) $newPostId;
            }

            $metaTitle = trim((string) ($translation['meta_title'] ?? ''));
            // Pass $null_values = false so Db::insert() keeps
            // Empty strings (PrestaShop would otherwise convert `''` to SQL NULL, which
            // would violate the NOT NULL constraint on the `content` column).
            \Db::getInstance()->insert('ever_blog_post_lang', [
                'id_ever_post' => $newPostId,
                'id_lang' => (int) ($translation['id_lang'] ?? 0),
                'title' => \pSQL($this->appendCopySuffix($title)),
                'meta_title' => '' !== $metaTitle ? \pSQL($this->appendCopySuffix($metaTitle)) : '',
                'meta_description' => \pSQL((string) ($translation['meta_description'] ?? '')),
                'link_rewrite' => \pSQL($this->buildCopyLinkRewrite($translation, $newPostId)),
                'content' => \pSQL((string) ($translation['content'] ?? ''), true),
                'excerpt' => \pSQL((string) ($translation['excerpt'] ?? ''), true),
            ], false);
        }
    }

    private function duplicateRelations(int $sourcePostId, int $newPostId): void
    {
        $this->duplicateRelation('ever_blog_post_category', 'id_ever_post_category', $sourcePostId, $newPostId);
        $this->duplicateRelation('ever_blog_post_tag', 'id_ever_post_tag', $sourcePostId, $newPostId);
        $this->duplicateRelation('ever_blog_post_product', 'id_ever_post_product', $sourcePostId, $newPostId);
    }

    private function duplicateRelation(string $table, string $relationColumn, int $sourcePostId, int $newPostId): void
    {
        \Db::getInstance()->execute(
            'INSERT IGNORE INTO `' . _DB_PREFIX_ . $table . '` (`' . $relationColumn . '`, `id_ever_post`)
             SELECT `' . $relationColumn . '`, ' . (int) $newPostId . '
             FROM `' . _DB_PREFIX_ . $table . '`
             WHERE `id_ever_post` = ' . (int) $sourcePostId
        );
    }

    private function ensureDefaultCategoryRelation(int $newPostId, int $defaultCategoryId): void
    {
        if ($defaultCategoryId <= 0) {
            return;
        }

        \Db::getInstance()->execute(
            'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ever_blog_post_category` (`id_ever_post_category`, `id_ever_post`) VALUES ('
            . (int) $defaultCategoryId . ', ' . (int) $newPostId . ')'
        );
    }

    private function duplicatePostImage(int $sourcePostId, int $newPostId, int $shopId, string $imageType): void
    {
        $sourceImage = $this->blogImageService->getBlogImage($sourcePostId, $shopId, $imageType);
        if (!\Validate::isLoadedObject($sourceImage)) {
            return;
        }

        $sourcePath = $this->resolveLocalImagePath((string) $sourceImage->image_link);
        if (null === $sourcePath || !is_file($sourcePath)) {
            return;
        }

        $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return;
        }

        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageType;
        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            \PrestaShopLogger::addLog('[everpsblog][PostDuplicator] Unable to create post image directory for ' . $imageType . '.', 2);

            return;
        }

        $targetFileName = sprintf('%d.%s', $newPostId, $extension);
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $targetFileName;
        if (!@copy($sourcePath, $targetPath)) {
            \PrestaShopLogger::addLog('[everpsblog][PostDuplicator] Unable to copy ' . $imageType . ' image.', 2);

            return;
        }

        $image = $this->blogImageService->createImageModel();
        $image->id_element = $newPostId;
        $image->id_shop = $shopId;
        $image->image_type = $imageType;
        $image->image_link = 'img/' . $imageType . '/' . $targetFileName;

        if ((bool) $image->save()) {
            $imageId = (int) ($image->id ?: $image->id_ever_image);
            if ($imageId > 0) {
                \Db::getInstance()->execute(
                    'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ever_blog_image_shop` (`id_ever_image`, `id_shop`) VALUES ('
                    . (int) $imageId . ', ' . (int) $shopId . ')'
                );
            }
            $this->blogImageService->clearCache();
        }
    }

    private function resolveLocalImagePath(string $imageLink): ?string
    {
        $imageLink = trim($imageLink);
        if ('' === $imageLink || 0 === strpos($imageLink, 'http://') || 0 === strpos($imageLink, 'https://')) {
            return null;
        }

        $relative = ltrim($imageLink, '/\\');
        $candidates = [
            rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative),
            rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, preg_replace('#^img/#', '', $relative)),
            rtrim(_PS_MODULE_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'everpsblog' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function buildCopyLinkRewrite(array $translation, int $newPostId): string
    {
        $base = trim((string) ($translation['link_rewrite'] ?? ''));
        if ('' === $base) {
            $base = trim((string) ($translation['title'] ?? 'article'));
        }

        $slug = \Tools::str2url($base . '-copie-' . (int) $newPostId);
        if ('' === $slug) {
            $slug = 'article-copie-' . (int) $newPostId;
        }

        return $this->truncate($slug, 255);
    }

    private function appendCopySuffix(string $value): string
    {
        $suffix = ' (copie)';
        $base = $this->truncate(trim($value), 255 - strlen($suffix));

        return $base . $suffix;
    }

    private function truncate(string $value, int $length): string
    {
        if ($length <= 0) {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return \mb_strlen($value) > $length ? \mb_substr($value, 0, $length) : $value;
        }

        return strlen($value) > $length ? substr($value, 0, $length) : $value;
    }

    private function nullableString($value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = (string) $value;

        return '' === $value ? null : \pSQL($value);
    }
}
