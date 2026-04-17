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
        $connection->insert('ever_blog_author', [
            'id_employee' => (int) $data['id_employee'],
            'id_shop' => (int) $data['id_shop'],
            'nickhandle' => (string) $data['nickhandle'],
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
            'allowed_groups' => null,
            'count' => 0,
        ]);

        $authorId = (int) $connection->lastInsertId();
        $this->replaceRelations($authorId, $data);

        return $authorId;
    }

    public function update(int $authorId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->update('ever_blog_author', [
            'id_employee' => (int) $data['id_employee'],
            'nickhandle' => (string) $data['nickhandle'],
            'active' => (int) $data['active'],
            'indexable' => (int) $data['indexable'],
            'follow' => (int) $data['follow'],
            'sitemap' => (int) $data['sitemap'],
        ], ['id_ever_author' => $authorId]);

        $this->replaceRelations($authorId, $data);
    }

    public function delete(int $authorId): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete('ever_blog_author_lang', ['id_ever_author' => $authorId]);
        $connection->delete('ever_blog_author_shop', ['id_ever_author' => $authorId]);
        $connection->delete('ever_blog_author', ['id_ever_author' => $authorId]);
    }

    private function replaceRelations(int $authorId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete('ever_blog_author_lang', ['id_ever_author' => $authorId]);
        $connection->delete('ever_blog_author_shop', ['id_ever_author' => $authorId]);
        $connection->insert('ever_blog_author_shop', ['id_ever_author' => $authorId, 'id_shop' => (int) $data['id_shop']]);

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $metaTitle = (string) ($data['meta_title_' . $langId] ?? $data['meta_title']);
            $metaDescription = (string) ($data['meta_description_' . $langId] ?? $data['meta_description']);
            $content = (string) ($data['bio_' . $langId] ?? $data['bio']);

            $connection->insert('ever_blog_author_lang', [
                'id_ever_author' => $authorId,
                'id_lang' => $langId,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'link_rewrite' => Tools::str2url((string) ($data['nickhandle'] ?? 'author-' . $authorId)),
                'content' => $content,
                'bottom_content' => (string) ($data['bottom_content_' . $langId] ?? ''),
            ]);
        }
    }
}
