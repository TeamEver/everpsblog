<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use EverPsBlogCategory;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject\PostCommandData;
use Tools;

class PostCommandDataBuilder
{
    /** @var int */
    private $shopId;
    /** @var int */
    private $unclassedCategoryId;

    public function __construct(int $shopId, int $unclassedCategoryId)
    {
        $this->shopId = $shopId;
        $this->unclassedCategoryId = $unclassedCategoryId;
    }

    public function buildFromRequestData(array $data): PostCommandData
    {
        $rootCategory = EverPsBlogCategory::getRootCategory();
        $dateAdd = isset($data['date_add']) && $data['date_add'] ? (string) $data['date_add'] : date('Y-m-d H:i:s');

        return new PostCommandData(
            $this->shopId,
            (int) ($data['id_author'] ?? 0),
            (int) ($data['id_default_category'] ?? 0),
            $this->unclassedCategoryId,
            (int) $rootCategory->id,
            (string) ($data['post_status'] ?? 'draft'),
            isset($data['psswd']) ? (string) $data['psswd'] : null,
            $dateAdd,
            (int) ($data['indexable'] ?? 0),
            (int) ($data['follow'] ?? 0),
            (int) ($data['sitemap'] ?? 0),
            (int) ($data['starred'] ?? 0),
            $this->toArray($data['post_categories'] ?? []),
            $this->toArray($data['allowed_groups'] ?? []),
            $this->toArray($data['post_tags'] ?? []),
            $this->toArray($data['post_products'] ?? []),
            $this->buildTranslations($data)
        );
    }

    private function buildTranslations(array $data): array
    {
        $translations = [];

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $title = (string) ($data['title_' . $idLang] ?? '');

            $translations[$idLang] = [
                'title' => $title,
                'content' => (string) ($data['content_' . $idLang] ?? ''),
                'excerpt' => (string) ($data['excerpt_' . $idLang] ?? ''),
                'meta_title' => (string) ($data['meta_title_' . $idLang] ?? ''),
                'meta_description' => (string) ($data['meta_description_' . $idLang] ?? ''),
                'link_rewrite' => Tools::str2url((string) ($data['link_rewrite_' . $idLang] ?? $title)),
            ];
        }

        return $translations;
    }

    private function toArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (null === $value || '' === $value) {
            return [];
        }

        return [$value];
    }
}
