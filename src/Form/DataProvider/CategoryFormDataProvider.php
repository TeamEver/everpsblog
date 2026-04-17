<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use Tools;

final class CategoryFormDataProvider
{
    /**
     * @return array<string, mixed>
     */
    public function getData(?int $id = null): array
    {
        $data = [
            'id' => $id,
            'title' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $title = (string) ($data['title_' . $langId] ?? $data['title']);
            $metaTitle = (string) ($data['meta_title_' . $langId] ?? $data['meta_title']);

            $data['title_' . $langId] = $title;
            $data['meta_title_' . $langId] = $metaTitle;
            $data['meta_description_' . $langId] = (string) ($data['meta_description_' . $langId] ?? $data['meta_description']);
            $data['link_rewrite_' . $langId] = (string) ($data['link_rewrite_' . $langId] ?? Tools::str2url($title ?: $metaTitle));
            $data['content_' . $langId] = (string) ($data['content_' . $langId] ?? $data['content']);
            $data['bottom_content_' . $langId] = (string) ($data['bottom_content_' . $langId] ?? $data['bottom_content']);
        }

        return $data;
    }
}
