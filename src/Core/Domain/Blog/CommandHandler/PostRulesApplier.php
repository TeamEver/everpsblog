<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use DateTimeImmutable;
use InvalidArgumentException;
use PrestaShop\Module\Everpsblog\Entity\Post;
use PrestaShop\Module\Everpsblog\Entity\PostCategory;
use PrestaShop\Module\Everpsblog\Entity\PostLang;
use PrestaShop\Module\Everpsblog\Entity\PostProduct;
use PrestaShop\Module\Everpsblog\Entity\PostShop;
use PrestaShop\Module\Everpsblog\Entity\PostTag;

class PostRulesApplier
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, array<int, object>>
     */
    public function apply(Post $post, array $payload): array
    {
        $post->setShopId((int) ($payload['shop_id'] ?? 0));
        $post->setAuthorId((int) ($payload['author_id'] ?? 0));
        $post->setDefaultCategoryId($this->resolveDefaultCategory($payload));
        $post->setCreatedAt(new DateTimeImmutable((string) $payload['date_add']));
        $post->setUpdatedAt(new DateTimeImmutable('now'));
        $post->setIndexable((bool) $payload['indexable']);
        $post->setFollow((bool) $payload['follow']);
        $post->setSitemap((bool) $payload['sitemap']);
        $post->setStarred((int) $payload['starred']);
        $post->setAllowedGroups($this->encodeArray($payload['allowed_groups']));
        $post->setStatus($this->resolveStatus($payload));
        $post->setPassword($this->resolvePassword($payload, $post->getStatus()));

        $postId = $post->getId();

        return [
            'langs' => $this->buildPostLangs($postId, $payload),
            'shops' => [PostShop::create($postId, (int) $payload['shop_id'])],
            'categories' => $this->buildPostCategories($postId, $payload, $post->getDefaultCategoryId()),
            'tags' => $this->buildPostTags($postId, $payload),
            'products' => $this->buildPostProducts($postId, $payload),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveDefaultCategory(array $payload): int
    {
        $defaultCategoryId = (int) $payload['default_category_id'];

        if (!$defaultCategoryId) {
            return (int) $payload['unclassed_category_id'];
        }

        if ($defaultCategoryId === (int) $payload['root_category_id']) {
            throw new InvalidArgumentException('Default category cannot be root category.');
        }

        return $defaultCategoryId;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveStatus(array $payload): string
    {
        $status = (string) $payload['post_status'];
        if ('trash' === $status) {
            return 'trash';
        }

        if ((string) $payload['date_add'] > date('Y-m-d H:i:s') && in_array($status, ['published', 'planned'], true)) {
            return 'planned';
        }

        if ('planned' === $status) {
            return 'published';
        }

        return $status;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolvePassword(array $payload, string $status): ?string
    {
        if ($status !== 'protected' || empty($payload['password'])) {
            return null;
        }

        return md5(_COOKIE_KEY_ . $payload['password']);
    }

    /**
     * @param mixed $value
     */
    private function encodeArray($value): ?string
    {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        return json_encode(array_values($value));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return PostLang[]
     */
    private function buildPostLangs(?int $postId, array $payload): array
    {
        $langs = [];

        foreach ($payload['translations'] as $langId => $translation) {
            $langs[] = PostLang::create(
                $postId,
                (int) $langId,
                (string) $translation['title'],
                (string) $translation['content'],
                (string) $translation['excerpt'],
                (string) $translation['meta_title'],
                (string) $translation['meta_description'],
                (string) $translation['link_rewrite']
            );
        }

        return $langs;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return PostCategory[]
     */
    private function buildPostCategories(?int $postId, array $payload, int $defaultCategoryId): array
    {
        $categories = $payload['post_categories'];
        if (!in_array($defaultCategoryId, $categories)) {
            $categories[] = $defaultCategoryId;
        }

        $categories = array_values(array_unique(array_map('intval', $categories)));

        return array_map(static function ($categoryId) use ($postId) {
            return PostCategory::create($postId, (int) $categoryId);
        }, $categories);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return PostTag[]
     */
    private function buildPostTags(?int $postId, array $payload): array
    {
        $tags = array_values(array_unique(array_map('intval', $payload['post_tags'])));

        return array_map(static function ($tagId) use ($postId) {
            return PostTag::create($postId, (int) $tagId);
        }, $tags);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return PostProduct[]
     */
    private function buildPostProducts(?int $postId, array $payload): array
    {
        $products = array_values(array_unique(array_map('intval', $payload['post_products'])));

        return array_map(static function ($productId) use ($postId) {
            return PostProduct::create($postId, (int) $productId);
        }, $products);
    }
}
