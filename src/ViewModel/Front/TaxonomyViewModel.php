<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\ViewModel\Front;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class TaxonomyViewModel
{
    public static function fromLegacy($item, string $type): array
    {
        $titleField = $type === 'author' ? 'nickhandle' : 'title';
        $excerpt = (string) ($item->excerpt ?? '');

        return [
            'id' => (int) ($item->id ?? 0),
            'title' => (string) ($item->{$titleField} ?? ''),
            'link_rewrite' => (string) ($item->link_rewrite ?? ''),
            'meta_title' => (string) ($item->meta_title ?? ''),
            'meta_description' => (string) ($item->meta_description ?? ''),
            'excerpt' => $excerpt,
            'summary' => '' !== trim($excerpt) ? $excerpt : (string) ($item->meta_description ?? ''),
            'content' => (string) ($item->content ?? ''),
            'bottom_content' => (string) ($item->bottom_content ?? ''),
            'count' => (int) ($item->count ?? 0),
            'indexable' => (bool) ($item->indexable ?? false),
            'follow' => (bool) ($item->follow ?? false),
            'type' => $type,
        ];
    }
}
