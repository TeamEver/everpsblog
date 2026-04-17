<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use PrestaShop\Module\Everpsblog\Repository\AuthorRepository;
use Tools;

final class AuthorFormDataProvider
{
    /** @var AuthorRepository */
    private $authorRepository;

    public function __construct(AuthorRepository $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(?int $id = null): array
    {
        if (null === $id) {
            return $this->getCreationData(null);
        }

        $entity = $this->authorRepository->find($id);
        if (null === $entity) {
            return $this->getCreationData($id);
        }

        $connection = $this->authorRepository->getEntityManager()->getConnection();
        /** @var array<string, mixed>|false $author */
        $author = $connection->fetchAssociative(
            'SELECT * FROM ever_blog_author WHERE id_ever_author = :id',
            ['id' => $id]
        );

        if (!$author) {
            return $this->getCreationData($id);
        }

        $data = [
            'id' => $id,
            'id_employee' => (int) ($author['id_employee'] ?? 0),
            'nickhandle' => (string) ($author['nickhandle'] ?? ''),
            'active' => (bool) ($author['active'] ?? 0),
            'indexable' => (bool) ($author['indexable'] ?? 0),
            'follow' => (bool) ($author['follow'] ?? 0),
            'sitemap' => (bool) ($author['sitemap'] ?? 0),
            'bio' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        $translations = $connection->fetchAllAssociative(
            'SELECT id_lang, meta_title, meta_description, link_rewrite, content, bottom_content
             FROM ever_blog_author_lang
             WHERE id_ever_author = :id',
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
            $metaTitle = (string) ($translation['meta_title'] ?? $data['meta_title']);
            $content = (string) ($translation['content'] ?? $data['bio']);

            $data['meta_title_' . $langId] = $metaTitle;
            $data['meta_description_' . $langId] = (string) ($translation['meta_description'] ?? $data['meta_description']);
            $data['link_rewrite_' . $langId] = (string) ($translation['link_rewrite'] ?? Tools::str2url($data['nickhandle'] ?: $metaTitle));
            $data['content_' . $langId] = $content;
            $data['bio_' . $langId] = $content;
            $data['bottom_content_' . $langId] = (string) ($translation['bottom_content'] ?? $data['bottom_content']);

            if ('' === $data['meta_title'] && '' === $data['bio']) {
                $data['meta_title'] = $metaTitle;
                $data['meta_description'] = (string) $data['meta_description_' . $langId];
                $data['link_rewrite'] = (string) $data['link_rewrite_' . $langId];
                $data['content'] = $content;
                $data['bio'] = $content;
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
            'id_employee' => 0,
            'nickhandle' => '',
            'active' => true,
            'indexable' => true,
            'follow' => true,
            'sitemap' => true,
            'bio' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $data['meta_title_' . $langId] = '';
            $data['meta_description_' . $langId] = '';
            $data['link_rewrite_' . $langId] = '';
            $data['content_' . $langId] = '';
            $data['bio_' . $langId] = '';
            $data['bottom_content_' . $langId] = '';
        }

        return $data;
    }
}
