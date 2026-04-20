<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository;

use Doctrine\ORM\EntityManagerInterface;

class CommentWriteRepository
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

        $connection->insert(_DB_PREFIX_ . 'ever_blog_comments', [
            'id_ever_post' => (int) $data['id_ever_post'],
            'id_lang' => (int) $data['id_lang'],
            'comment' => (string) $data['comment'],
            'name' => (string) $data['name'],
            'user_email' => (string) $data['user_email'],
            'active' => (int) $data['active'],
            'date_add' => $now,
            'date_upd' => $now,
        ]);

        return (int) $connection->lastInsertId();
    }

    public function update(int $commentId, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->update(_DB_PREFIX_ . 'ever_blog_comments', [
            'id_ever_post' => (int) $data['id_ever_post'],
            'id_lang' => (int) $data['id_lang'],
            'comment' => (string) $data['comment'],
            'name' => (string) $data['name'],
            'user_email' => (string) $data['user_email'],
            'active' => (int) $data['active'],
            'date_upd' => date('Y-m-d H:i:s'),
        ], ['id_ever_comment' => $commentId]);
    }

    public function delete(int $commentId): void
    {
        $this->entityManager->getConnection()->delete(_DB_PREFIX_ . 'ever_blog_comments', ['id_ever_comment' => $commentId]);
    }
}
