<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Tools;

class CategoryWriteRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(array $data): int
    {
        $connection = $this->entityManager->getConnection();
        $now = date('Y-m-d H:i:s');
        $connection->insert(_DB_PREFIX_ . 'ever_blog_category', [
            'id_parent_category' => $data['id_parent_category'],
            'id_shop' => (int) $data['id_shop'],
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
            'is_root_category' => (int) $data['is_root_category'],
            'count' => 0,
            'allowed_groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'category_products' => $this->encodeArray($data['category_products'] ?? null),
            'groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'date_add' => $now,
            'date_upd' => $now,
        ]);

        $categoryId = (int) $connection->lastInsertId();
        $this->replaceRelations($categoryId, $data);

        return $categoryId;
    }

    public function update(int $categoryId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->update(_DB_PREFIX_ . 'ever_blog_category', [
            'id_parent_category' => $data['id_parent_category'],
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
            'is_root_category' => (int) ($data['is_root_category'] ?? 0),
            'allowed_groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'category_products' => $this->encodeArray($data['category_products'] ?? null),
            'groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'date_upd' => date('Y-m-d H:i:s'),
        ], ['id_ever_category' => $categoryId]);

        $this->replaceRelations($categoryId, $data);
    }

    public function delete(int $categoryId): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete(_DB_PREFIX_ . 'ever_blog_category_lang', ['id_ever_category' => $categoryId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_category_shop', ['id_ever_category' => $categoryId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_category', ['id_ever_category' => $categoryId]);
    }

    private function replaceRelations(int $categoryId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete(_DB_PREFIX_ . 'ever_blog_category_lang', ['id_ever_category' => $categoryId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_category_shop', ['id_ever_category' => $categoryId]);
        $connection->insert(_DB_PREFIX_ . 'ever_blog_category_shop', ['id_ever_category' => $categoryId, 'id_shop' => (int) $data['id_shop']]);

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $title = (string) ($data['title_' . $langId] ?? $data['title']);
            $metaTitle = (string) ($data['meta_title_' . $langId] ?? $data['meta_title']);
            $metaDescription = (string) ($data['meta_description_' . $langId] ?? $data['meta_description']);
            $slug = (string) ($data['link_rewrite_' . $langId] ?? '');

            $connection->insert(_DB_PREFIX_ . 'ever_blog_category_lang', [
                'id_ever_category' => $categoryId,
                'id_lang' => $langId,
                'title' => $title,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'link_rewrite' => Tools::str2url($slug ?: ($title ?: $metaTitle)),
                'content' => (string) ($data['content_' . $langId] ?? $data['content']),
                'bottom_content' => (string) ($data['bottom_content_' . $langId] ?? $data['bottom_content']),
            ]);
        }
    }

    /**
     * @param mixed $value
     */
    private function encodeArray($value): ?string
    {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        return json_encode(array_values(array_map('intval', $value)));
    }
}
