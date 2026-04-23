<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service\Cache;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class BlogFrontCacheInvalidator
{
    /** @var BlogFrontCache */
    private $cache;

    /** @var BlogFrontCacheRelationResolver */
    private $resolver;

    public function __construct(?BlogFrontCache $cache = null, ?BlogFrontCacheRelationResolver $resolver = null)
    {
        $this->cache = $cache ?: new BlogFrontCache();
        $this->resolver = $resolver ?: new BlogFrontCacheRelationResolver();
    }

    /**
     * @param array{author_id?:int,default_category_id?:int,category_ids?:array<int,int>,tag_ids?:array<int,int>} $before
     * @param array{author_id?:int,default_category_id?:int,category_ids?:array<int,int>,tag_ids?:array<int,int>} $after
     */
    public function invalidatePostMutation(int $postId, array $before = [], array $after = []): void
    {
        if ($postId <= 0) {
            return;
        }

        if (empty($before) && empty($after)) {
            $after = $this->resolver->getPostSnapshot($postId);
        }

        $tags = [
            BlogFrontCacheTags::post($postId),
            BlogFrontCacheTags::postComments($postId),
            BlogFrontCacheTags::BLOG_LISTING,
            BlogFrontCacheTags::BLOG_STARRED,
            BlogFrontCacheTags::BLOG_SEARCH,
            BlogFrontCacheTags::BLOG_FEED,
        ];

        $authorIds = array_filter([
            (int) ($before['author_id'] ?? 0),
            (int) ($after['author_id'] ?? 0),
        ]);
        foreach ($authorIds as $authorId) {
            $tags[] = BlogFrontCacheTags::author($authorId);
        }

        $categoryIds = array_merge(
            (array) ($before['category_ids'] ?? []),
            (array) ($after['category_ids'] ?? []),
            array_filter([
                (int) ($before['default_category_id'] ?? 0),
                (int) ($after['default_category_id'] ?? 0),
            ])
        );
        foreach (array_values(array_unique(array_map('intval', $categoryIds))) as $categoryId) {
            if ($categoryId > 0) {
                $tags[] = BlogFrontCacheTags::category($categoryId);
            }
        }

        $tagIds = array_values(array_unique(array_map('intval', array_merge(
            (array) ($before['tag_ids'] ?? []),
            (array) ($after['tag_ids'] ?? [])
        ))));
        foreach ($tagIds as $tagId) {
            if ($tagId > 0) {
                $tags[] = BlogFrontCacheTags::tag($tagId);
            }
        }

        $this->cache->invalidateTags($tags);
    }

    /**
     * @param array{parent_id?:int} $before
     * @param array{parent_id?:int} $after
     */
    public function invalidateCategoryMutation(int $categoryId, array $before = [], array $after = []): void
    {
        if ($categoryId <= 0) {
            return;
        }

        $tags = [
            BlogFrontCacheTags::category($categoryId),
            BlogFrontCacheTags::BLOG_CATEGORIES_INDEX,
            BlogFrontCacheTags::BLOG_LISTING,
            BlogFrontCacheTags::BLOG_FEED,
        ];

        foreach ([(int) ($before['parent_id'] ?? 0), (int) ($after['parent_id'] ?? 0)] as $parentId) {
            if ($parentId > 0) {
                $tags[] = BlogFrontCacheTags::category($parentId);
            }
        }

        foreach ($this->resolver->listPostIdsByCategory($categoryId) as $postId) {
            $tags[] = BlogFrontCacheTags::post($postId);
            $tags[] = BlogFrontCacheTags::postComments($postId);
        }

        $this->cache->invalidateTags($tags);
    }

    public function invalidateTagMutation(int $tagId): void
    {
        if ($tagId <= 0) {
            return;
        }

        $tags = [
            BlogFrontCacheTags::tag($tagId),
            BlogFrontCacheTags::BLOG_TAGS_INDEX,
            BlogFrontCacheTags::BLOG_LISTING,
            BlogFrontCacheTags::BLOG_SEARCH,
            BlogFrontCacheTags::BLOG_FEED,
        ];

        foreach ($this->resolver->listPostIdsByTag($tagId) as $postId) {
            $tags[] = BlogFrontCacheTags::post($postId);
        }

        $this->cache->invalidateTags($tags);
    }

    /**
     * @param int[] $postIds
     */
    public function invalidateAuthorMutation(int $authorId, array $postIds = []): void
    {
        if ($authorId <= 0 && empty($postIds)) {
            return;
        }

        if ($authorId > 0 && empty($postIds)) {
            $postIds = $this->resolver->listPostIdsByAuthor($authorId);
        }

        $tags = [
            BlogFrontCacheTags::BLOG_LISTING,
            BlogFrontCacheTags::BLOG_SEARCH,
            BlogFrontCacheTags::BLOG_FEED,
        ];

        if ($authorId > 0) {
            $tags[] = BlogFrontCacheTags::author($authorId);
        }

        foreach (array_values(array_unique(array_map('intval', $postIds))) as $postId) {
            if ($postId <= 0) {
                continue;
            }

            $tags[] = BlogFrontCacheTags::post($postId);
            $tags[] = BlogFrontCacheTags::postComments($postId);
        }

        $this->cache->invalidateTags($tags);
    }

    public function invalidateCommentMutation(int $commentId = 0, int $postId = 0): void
    {
        if ($commentId > 0 && $postId <= 0) {
            $postId = $this->resolver->getCommentPostId($commentId);
        }

        $tags = [
            BlogFrontCacheTags::BLOG_COMMENTS_CUSTOMER,
        ];

        if ($commentId > 0) {
            $tags[] = BlogFrontCacheTags::comment($commentId);
        }

        if ($postId > 0) {
            $tags[] = BlogFrontCacheTags::postComments($postId);
        }

        $this->cache->invalidateTags($tags);
    }

    public function invalidateImageMutation(int $elementId, string $imageType): void
    {
        $imageType = trim($imageType);
        if ($elementId <= 0 || '' === $imageType) {
            return;
        }

        switch ($imageType) {
            case 'post':
            case 'post_banner':
                $this->invalidatePostMutation($elementId);
                break;
            case 'category':
            case 'category_banner':
                $this->invalidateCategoryMutation($elementId, [], $this->resolver->getCategorySnapshot($elementId));
                break;
            case 'tag':
            case 'tag_banner':
                $this->invalidateTagMutation($elementId);
                break;
            case 'author':
            case 'author_banner':
                $this->invalidateAuthorMutation($elementId);
                break;
            default:
                $this->invalidateAll();
        }
    }

    public function invalidateConfiguration(): void
    {
        $this->cache->clear();
    }

    public function invalidateAll(): void
    {
        $this->cache->clear();
    }
}
