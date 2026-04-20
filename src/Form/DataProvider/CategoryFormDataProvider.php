<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Everpsblog\Repository\CategoryRepository;
use Tools;

final class CategoryFormDataProvider
{
    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
    {
        $this->categoryRepository = $categoryRepository;
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

        $entity = $this->categoryRepository->find($id);
        if (null === $entity) {
            return $this->getCreationData($id);
        }

        $connection = $this->entityManager->getConnection();
        /** @var array<string, mixed>|false $category */
        $category = $connection->fetchAssociative(
            'SELECT * FROM `' . _DB_PREFIX_ . 'ever_blog_category` WHERE id_ever_category = :id',
            ['id' => $id]
        );

        if (!$category) {
            return $this->getCreationData($id);
        }

        $data = [
            'id' => $id,
            'id_parent_category' => isset($category['id_parent_category']) ? (int) $category['id_parent_category'] : null,
            'active' => (bool) ($category['active'] ?? 0),
            'indexable' => (bool) ($category['indexable'] ?? 0),
            'follow' => (bool) ($category['follow'] ?? 0),
            'sitemap' => (bool) ($category['sitemap'] ?? 0),
            'is_root_category' => (bool) ($category['is_root_category'] ?? 0),
            'count' => (int) ($category['count'] ?? 0),
            'allowed_groups' => $this->normalizeIntCollection($category['allowed_groups'] ?? null),
            'category_products' => $this->normalizeIntCollection($category['category_products'] ?? null),
            'title' => '',
            'meta_title' => '',
            'meta_description' => '',
            'link_rewrite' => '',
            'content' => '',
            'bottom_content' => '',
        ];

        $translations = $connection->fetchAllAssociative(
            'SELECT id_lang, title, meta_title, meta_description, link_rewrite, content, bottom_content
             FROM `' . _DB_PREFIX_ . 'ever_blog_category_lang`
             WHERE id_ever_category = :id',
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
            'id_parent_category' => null,
            'active' => true,
            'indexable' => true,
            'follow' => true,
            'sitemap' => true,
            'is_root_category' => false,
            'count' => 0,
            'allowed_groups' => [],
            'category_products' => [],
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
