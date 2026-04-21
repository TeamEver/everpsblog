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

    /**
     * Reassigns every post owned by $fromAuthorId to $toAuthorId.
     */
    public function reassignPostsAuthor(int $fromAuthorId, int $toAuthorId): int
    {
        if ($fromAuthorId <= 0 || $toAuthorId <= 0 || $fromAuthorId === $toAuthorId) {
            return 0;
        }

        return (int) $this->entityManager->getConnection()->update(
            _DB_PREFIX_ . 'ever_blog_post',
            ['id_author' => $toAuthorId],
            ['id_author' => $fromAuthorId]
        );
    }

    /**
     * Clears the author on any post still linked to $authorId (sets id_author = 0).
     */
    public function clearAuthorForPosts(int $authorId): int
    {
        if ($authorId <= 0) {
            return 0;
        }

        return (int) $this->entityManager->getConnection()->update(
            _DB_PREFIX_ . 'ever_blog_post',
            ['id_author' => 0],
            ['id_author' => $authorId]
        );
    }

    public function countPostsForAuthor(int $authorId): int
    {
        if ($authorId <= 0) {
            return 0;
        }

        return (int) $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'ever_blog_post` WHERE id_author = :authorId',
            ['authorId' => $authorId]
        );
    }

    public function countOtherAuthors(int $excludeAuthorId): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'ever_blog_author` WHERE id_ever_author <> :authorId',
            ['authorId' => $excludeAuthorId]
        );
    }

    /**
     * @return array<int, array{id_ever_author:int,nickhandle:string}>
     */
    public function listOtherAuthors(int $excludeAuthorId): array
    {
        $rows = $this->entityManager->getConnection()->fetchAllAssociative(
            'SELECT id_ever_author, nickhandle
             FROM `' . _DB_PREFIX_ . 'ever_blog_author`
             WHERE id_ever_author <> :authorId
             ORDER BY nickhandle ASC',
            ['authorId' => $excludeAuthorId]
        );

        return array_map(static function ($row) {
            return [
                'id_ever_author' => (int) $row['id_ever_author'],
                'nickhandle' => (string) $row['nickhandle'],
            ];
        }, $rows);
    }

    private function replaceRelations(int $authorId, array $data): void
    {
        $this->ensureAuthorLangExcerptColumn();

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
                'excerpt' => (string) ($data['excerpt_' . $langId] ?? ($data['excerpt'] ?? '')),
                'content' => $content,
                'bottom_content' => (string) ($data['bottom_content_' . $langId] ?? ''),
            ]);
        }
    }

    private function ensureAuthorLangExcerptColumn(): void
    {
        static $done = false;
        if ($done) {
            return;
        }

        $connection = $this->entityManager->getConnection();
        try {
            $exists = (bool) $connection->fetchAssociative(
                'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'ever_blog_author_lang` LIKE "excerpt"'
            );
            if (!$exists) {
                $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'ever_blog_author_lang`
                    ADD `excerpt` varchar(255) DEFAULT NULL AFTER `content`';
                if (method_exists($connection, 'executeStatement')) {
                    $connection->executeStatement($sql);
                } else {
                    $connection->executeUpdate($sql);
                }
            }
            $done = true;
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][AuthorWriteRepository::ensureAuthorLangExcerptColumn] ' . $exception->getMessage(),
                3
            );
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
