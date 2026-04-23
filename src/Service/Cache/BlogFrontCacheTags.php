<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service\Cache;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class BlogFrontCacheTags
{
    public const BLOG_LISTING = 'listing.blog';
    public const BLOG_STARRED = 'listing.blog.starred';
    public const BLOG_SEARCH = 'listing.blog.search';
    public const BLOG_FEED = 'listing.blog.feed';
    public const BLOG_CATEGORIES_INDEX = 'listing.blog.categories';
    public const BLOG_TAGS_INDEX = 'listing.blog.tags';
    public const BLOG_COMMENTS_CUSTOMER = 'listing.blog.comments.customer';
    public const BLOG_CONFIGURATION = 'blog.configuration';

    public static function post(int $postId): string
    {
        return 'entity.post.' . max(0, $postId);
    }

    public static function postComments(int $postId): string
    {
        return 'entity.post.comments.' . max(0, $postId);
    }

    public static function category(int $categoryId): string
    {
        return 'entity.category.' . max(0, $categoryId);
    }

    public static function tag(int $tagId): string
    {
        return 'entity.tag.' . max(0, $tagId);
    }

    public static function author(int $authorId): string
    {
        return 'entity.author.' . max(0, $authorId);
    }

    public static function comment(int $commentId): string
    {
        return 'entity.comment.' . max(0, $commentId);
    }
}
