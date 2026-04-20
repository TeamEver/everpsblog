<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Everpsblog\Repository\AuthorRepository;
use Tools;

final class AuthorFormDataProvider
{
    /** @var AuthorRepository */
    private $authorRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(AuthorRepository $authorRepository, EntityManagerInterface $entityManager)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $entityManager;
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

        $connection = $this->entityManager->getConnection();
        /** @var array<string, mixed>|false $author */
        $author = $connection->fetchAssociative(
            'SELECT * FROM `' . _DB_PREFIX_ . 'ever_blog_author` WHERE id_ever_author = :id',
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
            'twitter' => (string) ($author['twitter'] ?? ''),
            'facebook' => (string) ($author['facebook'] ?? ''),
            'linkedin' => (string) ($author['linkedin'] ?? ''),
            'count' => (int) ($author['count'] ?? 0),
            'allowed_groups' => $this->normalizeIntCollection($author['allowed_groups'] ?? null),
            'author_products' => $this->normalizeIntCollection($author['author_products'] ?? null),
            'bio' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        $translations = $connection->fetchAllAssociative(
            'SELECT id_lang, meta_title, meta_description, link_rewrite, content, bottom_content
             FROM `' . _DB_PREFIX_ . 'ever_blog_author_lang`
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
            'twitter' => '',
            'facebook' => '',
            'linkedin' => '',
            'count' => 0,
            'allowed_groups' => [],
            'author_products' => [],
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

    /**
     * @param mixed $value
     *
     * @return int[]
     */
    private function normalizeIntCollection($value): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_map('intval', $value));
        }

        $decoded = json_decode((string) $value, true);
        if (is_array($decoded)) {
            return array_values(array_map('intval', $decoded));
        }

        $items = array_filter(array_map('trim', explode(',', (string) $value)), static function ($item) {
            return '' !== $item;
        });

        return array_values(array_map('intval', $items));
    }
}
