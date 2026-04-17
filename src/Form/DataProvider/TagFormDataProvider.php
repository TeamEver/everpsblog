<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use PrestaShop\Module\Everpsblog\Repository\TagRepository;
use Tools;

final class TagFormDataProvider
{
    /** @var TagRepository */
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(?int $id = null): array
    {
        if (null === $id) {
            return $this->getCreationData(null);
        }

        $entity = $this->tagRepository->find($id);
        if (null === $entity) {
            return $this->getCreationData($id);
        }

        $connection = $this->tagRepository->getEntityManager()->getConnection();
        /** @var array<string, mixed>|false $tag */
        $tag = $connection->fetchAssociative(
            'SELECT * FROM ever_blog_tag WHERE id_ever_tag = :id',
            ['id' => $id]
        );
        if (!$tag) {
            return $this->getCreationData($id);
        }

        $data = [
            'id' => $id,
            'active' => (bool) ($tag['active'] ?? 0),
            'indexable' => (bool) ($tag['indexable'] ?? 0),
            'follow' => (bool) ($tag['follow'] ?? 0),
            'sitemap' => (bool) ($tag['sitemap'] ?? 0),
            'title' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        $translations = $connection->fetchAllAssociative(
            'SELECT id_lang, title, meta_title, meta_description, link_rewrite, content, bottom_content
             FROM ever_blog_tag_lang
             WHERE id_ever_tag = :id',
            ['id' => $id]
        );
        /** @var array<int, array<string, mixed>> $translationsByLang */
        $translationsByLang = [];
        foreach ($translations as $translation) {
            $translationsByLang[(int) $translation['id_lang']] = $translation;
        }

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $translation = $translationsByLang[$langId] ?? [];
            $title = (string) ($translation['title'] ?? $data['title']);
            $metaTitle = (string) ($translation['meta_title'] ?? $data['meta_title']);

            $data['title_' . $langId] = $title;
            $data['meta_title_' . $langId] = $metaTitle;
            $data['meta_description_' . $langId] = (string) ($translation['meta_description'] ?? $data['meta_description']);
            $data['link_rewrite_' . $langId] = (string) ($translation['link_rewrite'] ?? Tools::str2url($title ?: $metaTitle));
            $data['content_' . $langId] = (string) ($translation['content'] ?? $data['content']);
            $data['bottom_content_' . $langId] = (string) ($translation['bottom_content'] ?? $data['bottom_content']);

            if ('' === $data['title']) {
                $data['title'] = $title;
                $data['meta_title'] = $metaTitle;
                $data['meta_description'] = (string) $data['meta_description_' . $langId];
                $data['link_rewrite'] = (string) $data['link_rewrite_' . $langId];
                $data['content'] = (string) $data['content_' . $langId];
                $data['bottom_content'] = (string) $data['bottom_content_' . $langId];
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function getCreationData(?int $id): array
    {
        $data = [
            'id' => $id,
            'active' => true,
            'indexable' => true,
            'follow' => true,
            'sitemap' => true,
            'title' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $data['title_' . $langId] = '';
            $data['meta_title_' . $langId] = '';
            $data['meta_description_' . $langId] = '';
            $data['link_rewrite_' . $langId] = '';
            $data['content_' . $langId] = '';
            $data['bottom_content_' . $langId] = '';
        }

        return $data;
    }
}
