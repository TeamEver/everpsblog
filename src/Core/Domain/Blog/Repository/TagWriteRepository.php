<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Tools;

class TagWriteRepository
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
        $connection->insert(_DB_PREFIX_ . 'ever_blog_tag', [
            'id_shop' => (int) $data['id_shop'],
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
            'allowed_groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'tag_products' => $this->encodeArray($data['tag_products'] ?? null),
            'count' => 0,
        ]);

        $tagId = (int) $connection->lastInsertId();
        $this->replaceRelations($tagId, $data);

        return $tagId;
    }

    public function update(int $tagId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->update(_DB_PREFIX_ . 'ever_blog_tag', [
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
            'allowed_groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'tag_products' => $this->encodeArray($data['tag_products'] ?? null),
        ], ['id_ever_tag' => $tagId]);

        $this->replaceRelations($tagId, $data);
    }

    public function delete(int $tagId): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete(_DB_PREFIX_ . 'ever_blog_tag_lang', ['id_ever_tag' => $tagId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_tag_shop', ['id_ever_tag' => $tagId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_tag', ['id_ever_tag' => $tagId]);
    }

    private function replaceRelations(int $tagId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete(_DB_PREFIX_ . 'ever_blog_tag_lang', ['id_ever_tag' => $tagId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_tag_shop', ['id_ever_tag' => $tagId]);
        $connection->insert(_DB_PREFIX_ . 'ever_blog_tag_shop', ['id_ever_tag' => $tagId, 'id_shop' => (int) $data['id_shop']]);

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $title = (string) ($data['title_' . $langId] ?? $data['title']);
            $metaTitle = (string) ($data['meta_title_' . $langId] ?? $data['meta_title']);
            $metaDescription = (string) ($data['meta_description_' . $langId] ?? $data['meta_description']);
            $slug = (string) ($data['link_rewrite_' . $langId] ?? '');

            $connection->insert(_DB_PREFIX_ . 'ever_blog_tag_lang', [
                'id_ever_tag' => $tagId,
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
