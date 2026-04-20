<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Everpsblog\Repository\PostRepository;
use Tools;

final class PostFormDataProvider
{
    /** @var PostRepository */
    private $postRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        $this->postRepository = $postRepository;
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

        $entity = $this->postRepository->find($id);
        if (null === $entity) {
            return $this->getCreationData($id);
        }

        $connection = $this->entityManager->getConnection();
        /** @var array<string, mixed>|false $post */
        $post = $connection->fetchAssociative(
            'SELECT * FROM `' . _DB_PREFIX_ . 'ever_blog_post` WHERE id_ever_post = :id',
            ['id' => $id]
        );

        if (!$post) {
            return $this->getCreationData($id);
        }

        $data = [
            'id' => $id,
            'post_status' => (string) ($post['post_status'] ?? 'draft'),
            'date_add' => isset($post['date_add']) ? (string) $post['date_add'] : '',
            'id_author' => (int) ($post['id_author'] ?? 0),
            'id_default_category' => (int) ($post['id_default_category'] ?? 0),
            'indexable' => (bool) ($post['indexable'] ?? 0),
            'follow' => (bool) ($post['follow'] ?? 0),
            'sitemap' => (bool) ($post['sitemap'] ?? 0),
            'starred' => (bool) ($post['starred'] ?? 0),
            'psswd' => '',
            'post_categories' => [],
            'post_tags' => [],
            'post_products' => [],
            'allowed_groups' => $this->normalizeIntCollection($post['allowed_groups'] ?? null),
            'title' => '',
            'content' => '',
            'excerpt' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
        ];

        $data['post_categories'] = array_values(array_map('intval', array_column($connection->fetchAllAssociative(
            'SELECT id_ever_post_category FROM `' . _DB_PREFIX_ . 'ever_blog_post_category` WHERE id_ever_post = :id',
            ['id' => $id]
        ), 'id_ever_post_category')));
        $data['post_tags'] = array_values(array_map('intval', array_column($connection->fetchAllAssociative(
            'SELECT id_ever_post_tag FROM `' . _DB_PREFIX_ . 'ever_blog_post_tag` WHERE id_ever_post = :id',
            ['id' => $id]
        ), 'id_ever_post_tag')));
        $data['post_products'] = array_values(array_map('intval', array_column($connection->fetchAllAssociative(
            'SELECT id_ever_post_product FROM `' . _DB_PREFIX_ . 'ever_blog_post_product` WHERE id_ever_post = :id',
            ['id' => $id]
        ), 'id_ever_post_product')));

        $translations = $connection->fetchAllAssociative(
            'SELECT id_lang, title, content, excerpt, meta_title, meta_description, link_rewrite
             FROM `' . _DB_PREFIX_ . 'ever_blog_post_lang`
             WHERE id_ever_post = :id',
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
            $data['content_' . $langId] = (string) ($translation['content'] ?? $data['content']);
            $data['excerpt_' . $langId] = (string) ($translation['excerpt'] ?? $data['excerpt']);
            $data['meta_title_' . $langId] = $metaTitle;
            $data['meta_description_' . $langId] = (string) ($translation['meta_description'] ?? $data['meta_description']);
            $data['link_rewrite_' . $langId] = (string) ($translation['link_rewrite'] ?? Tools::str2url($title ?: $metaTitle));

            if ('' === $data['title']) {
                $data['title'] = $title;
                $data['content'] = (string) $data['content_' . $langId];
                $data['excerpt'] = (string) $data['excerpt_' . $langId];
                $data['meta_title'] = $metaTitle;
                $data['meta_description'] = (string) $data['meta_description_' . $langId];
                $data['link_rewrite'] = (string) $data['link_rewrite_' . $langId];
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
            'post_status' => 'draft',
            'date_add' => date('Y-m-d H:i:s'),
            'id_author' => 0,
            'id_default_category' => 0,
            'indexable' => true,
            'follow' => true,
            'sitemap' => true,
            'starred' => false,
            'psswd' => '',
            'post_categories' => [],
            'post_tags' => [],
            'post_products' => [],
            'allowed_groups' => [],
            'title' => '',
            'content' => '',
            'excerpt' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
        ];

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $data['title_' . $langId] = '';
            $data['content_' . $langId] = '';
            $data['excerpt_' . $langId] = '';
            $data['meta_title_' . $langId] = '';
            $data['meta_description_' . $langId] = '';
            $data['link_rewrite_' . $langId] = '';
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
