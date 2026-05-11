<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Everpsblog\Repository\CommentRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class CommentFormDataProvider
{
    /** @var CommentRepository */
    private $commentRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(CommentRepository $commentRepository, EntityManagerInterface $entityManager)
    {
        $this->commentRepository = $commentRepository;
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

        $entity = $this->commentRepository->find($id);
        if (null === $entity) {
            return $this->getCreationData($id);
        }

        $connection = $this->entityManager->getConnection();
        /** @var array<string, mixed>|false $comment */
        $comment = $connection->fetchAssociative(
            'SELECT * FROM `' . _DB_PREFIX_ . 'ever_blog_comments` WHERE id_ever_comment = :id',
            ['id' => $id]
        );

        if (!$comment) {
            return $this->getCreationData($id);
        }

        return [
            'id' => $id,
            'id_ever_post' => (int) ($comment['id_ever_post'] ?? 0),
            'id_lang' => (int) ($comment['id_lang'] ?? 0),
            'name' => (string) ($comment['name'] ?? ''),
            'comment' => (string) ($comment['comment'] ?? ''),
            'user_email' => (string) ($comment['user_email'] ?? ''),
            'active' => (bool) ($comment['active'] ?? 0),
            'nickname' => (string) ($comment['name'] ?? ''),
            'content' => (string) ($comment['comment'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getCreationData(?int $id): array
    {
        return [
            'id' => $id,
            'id_ever_post' => 0,
            'id_lang' => 0,
            'name' => '',
            'comment' => '',
            'user_email' => '',
            'active' => true,
            'nickname' => '',
            'content' => '',
        ];
    }
}
