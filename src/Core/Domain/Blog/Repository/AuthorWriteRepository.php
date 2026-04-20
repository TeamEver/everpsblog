<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Tools;

class AuthorWriteRepository
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
        $connection->insert(_DB_PREFIX_ . 'ever_blog_author', [
            'id_employee' => (int) $data['id_employee'],
            'id_shop' => (int) $data['id_shop'],
            'nickhandle' => (string) $data['nickhandle'],
            'twitter' => (string) ($data['twitter'] ?? ''),
            'facebook' => (string) ($data['facebook'] ?? ''),
            'linkedin' => (string) ($data['linkedin'] ?? ''),
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
            'allowed_groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'author_products' => $this->encodeArray($data['author_products'] ?? null),
            'count' => 0,
        ]);

        $authorId = (int) $connection->lastInsertId();
        $this->replaceRelations($authorId, $data);

        return $authorId;
    }

    public function update(int $authorId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->update(_DB_PREFIX_ . 'ever_blog_author', [
            'id_employee' => (int) $data['id_employee'],
            'nickhandle' => (string) $data['nickhandle'],
            'twitter' => (string) ($data['twitter'] ?? ''),
            'facebook' => (string) ($data['facebook'] ?? ''),
            'linkedin' => (string) ($data['linkedin'] ?? ''),
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
            'allowed_groups' => $this->encodeArray($data['allowed_groups'] ?? null),
            'author_products' => $this->encodeArray($data['author_products'] ?? null),
        ], ['id_ever_author' => $authorId]);

        $this->replaceRelations($authorId, $data);
    }

    public function delete(int $authorId): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete(_DB_PREFIX_ . 'ever_blog_author_lang', ['id_ever_author' => $authorId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_author_shop', ['id_ever_author' => $authorId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_author', ['id_ever_author' => $authorId]);
    }

    private function replaceRelations(int $authorId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete(_DB_PREFIX_ . 'ever_blog_author_lang', ['id_ever_author' => $authorId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_author_shop', ['id_ever_author' => $authorId]);
        $connection->insert(_DB_PREFIX_ . 'ever_blog_author_shop', ['id_ever_author' => $authorId, 'id_shop' => (int) $data['id_shop']]);

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $metaTitle = (string) ($data['meta_title_' . $langId] ?? $data['meta_title']);
            $metaDescription = (string) ($data['meta_description_' . $langId] ?? $data['meta_description']);
            $content = (string) ($data['content_' . $langId] ?? ($data['bio_' . $langId] ?? $data['bio']));
            $slug = (string) ($data['link_rewrite_' . $langId] ?? '');

            $connection->insert(_DB_PREFIX_ . 'ever_blog_author_lang', [
                'id_ever_author' => $authorId,
                'id_lang' => $langId,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'link_rewrite' => Tools::str2url($slug ?: (string) ($data['nickhandle'] ?? 'author-' . $authorId)),
                'content' => $content,
                'bottom_content' => (string) ($data['bottom_content_' . $langId] ?? ''),
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
