<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\ViewModel\Front;

final class PostViewModel
{
    public static function fromLegacy($post): array
    {
        return [
            'id' => (int) ($post->id ?? $post['id_ever_post'] ?? 0),
            'title' => (string) ($post->title ?? $post['title'] ?? ''),
            'link_rewrite' => (string) ($post->link_rewrite ?? $post['link_rewrite'] ?? ''),
            'meta_title' => (string) ($post->meta_title ?? $post['meta_title'] ?? ''),
            'meta_description' => (string) ($post->meta_description ?? $post['meta_description'] ?? ''),
            'excerpt' => (string) ($post->excerpt ?? $post['excerpt'] ?? ''),
            'content' => (string) ($post->content ?? $post['content'] ?? ''),
            'date_add' => (string) ($post->date_add ?? $post['date_add'] ?? ''),
            'url' => (string) ($post->url ?? $post['url'] ?? ''),
            'featured' => (bool) ($post->is_featured ?? $post['is_featured'] ?? false),
            'comment_count' => (int) ($post->comment_count ?? $post['comment_count'] ?? 0),
            'cover' => (string) ($post->cover ?? $post['cover'] ?? ''),
            'author' => [
                'id' => (int) ($post->id_ever_author ?? $post['id_ever_author'] ?? 0),
                'name' => (string) ($post->nickhandle ?? $post['nickhandle'] ?? ''),
            ],
        ];
    }

    public static function listFromLegacy(array $posts): array
    {
        return array_values(array_map(function ($post) {
            return self::fromLegacy($post);
        }, $posts));
    }
}
