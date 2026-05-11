<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\ViewModel\Front;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class PostViewModel
{
    public static function fromLegacy($post): array
    {
        $id = (int) self::value($post, 'id', self::value($post, 'id_ever_post', 0));
        $idEverPost = (int) self::value($post, 'id_ever_post', $id);
        $idAuthor = (int) self::value($post, 'id_ever_author', self::value($post, 'id_author', 0));
        $featuredThumb = (string) self::value($post, 'featured_thumb', self::value($post, 'cover', ''));
        $featuredImage = (string) self::value($post, 'featured_image', $featuredThumb);
        $title = (string) self::value($post, 'title', self::value($post, 'meta_title', ''));
        $content = (string) self::value($post, 'content', '');
        $excerpt = (string) self::value($post, 'excerpt', self::value($post, 'meta_description', ''));
        $summary = (string) self::value($post, 'summary', '');
        if ('' === trim($summary)) {
            $summary = '' !== trim($excerpt) ? $excerpt : self::summaryFromContent($content);
        }
        if (self::isPlaceholderSummary($summary)) {
            $summary = self::summaryFromContent($content);
        }
        if (self::isPlaceholderSummary($summary)) {
            $summary = '';
        }

        return [
            'id' => $id,
            'id_ever_post' => $idEverPost,
            'id_default_category' => (int) self::value($post, 'id_default_category', 0),
            'id_ever_author' => $idAuthor,
            'title' => $title,
            'link_rewrite' => (string) self::value($post, 'link_rewrite', ''),
            'meta_title' => (string) self::value($post, 'meta_title', ''),
            'meta_description' => (string) self::value($post, 'meta_description', ''),
            'excerpt' => $summary,
            'summary' => $summary,
            'content' => $content,
            'date_add' => (string) self::value($post, 'date_add', ''),
            'date_upd' => (string) self::value($post, 'date_upd', ''),
            'post_status' => (string) self::value($post, 'post_status', ''),
            'active' => (bool) self::value($post, 'active', false),
            'starred' => (bool) self::value($post, 'starred', false),
            'count' => (int) self::value($post, 'count', 0),
            'url' => (string) self::value($post, 'url', ''),
            'link' => (string) self::value($post, 'url', ''),
            'featured' => (bool) self::value($post, 'is_featured', self::value($post, 'starred', false)),
            'comment_count' => (int) self::value($post, 'comment_count', 0),
            'cover' => $featuredThumb,
            'featured_thumb' => $featuredThumb,
            'featured_image' => $featuredImage,
            'author' => [
                'id' => $idAuthor,
                'name' => (string) self::value($post, 'nickhandle', ''),
            ],
        ];
    }

    public static function listFromLegacy(array $posts): array
    {
        return array_values(array_map(function ($post) {
            return self::fromLegacy($post);
        }, $posts));
    }

    private static function value($post, string $key, $default = null)
    {
        if (is_array($post)) {
            return array_key_exists($key, $post) ? $post[$key] : $default;
        }

        if (is_object($post)) {
            return isset($post->{$key}) ? $post->{$key} : $default;
        }

        return $default;
    }

    private static function summaryFromContent(string $content): string
    {
        $summary = trim(strip_tags($content));
        if (function_exists('mb_substr')) {
            return mb_substr($summary, 0, 300);
        }

        return substr($summary, 0, 300);
    }

    private static function isPlaceholderSummary(string $summary): bool
    {
        $summary = html_entity_decode($summary, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $summary = trim(strip_tags($summary));
        $summary = preg_replace('/\s+/u', ' ', $summary);
        $summary = is_string($summary) ? trim($summary) : '';

        if (function_exists('iconv')) {
            $asciiSummary = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $summary);
            if (is_string($asciiSummary)) {
                $summary = $asciiSummary;
            }
        }

        $summary = strtolower($summary);
        $summary = preg_replace('/[^a-z0-9]+/', '-', $summary);
        $summary = is_string($summary) ? trim($summary, '-') : '';

        return in_array($summary, ['resume', 'resume-de-l-article'], true);
    }
}
