<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use Tools;

final class AuthorFormDataProvider
{
    /**
     * @return array<string, mixed>
     */
    public function getData(?int $id = null): array
    {
        $data = [
            'id' => $id,
            'nickhandle' => '',
            'bio' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $metaTitle = (string) ($data['meta_title_' . $langId] ?? $data['meta_title']);
            $content = (string) ($data['content_' . $langId] ?? ($data['bio_' . $langId] ?? $data['bio']));

            $data['meta_title_' . $langId] = $metaTitle;
            $data['meta_description_' . $langId] = (string) ($data['meta_description_' . $langId] ?? $data['meta_description']);
            $data['link_rewrite_' . $langId] = (string) ($data['link_rewrite_' . $langId] ?? Tools::str2url($data['nickhandle'] ?: $metaTitle));
            $data['content_' . $langId] = $content;
            $data['bio_' . $langId] = $content;
            $data['bottom_content_' . $langId] = (string) ($data['bottom_content_' . $langId] ?? $data['bottom_content']);
        }

        return $data;
    }
}
