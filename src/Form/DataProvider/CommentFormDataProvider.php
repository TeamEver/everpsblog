<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

use PrestaShop\Module\Everpsblog\Repository\CommentRepository;

final class CommentFormDataProvider
{
    /** @var CommentRepository */
    private $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
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

        $connection = $this->commentRepository->getEntityManager()->getConnection();
        /** @var array<string, mixed>|false $comment */
        $comment = $connection->fetchAssociative(
            'SELECT * FROM ever_blog_comments WHERE id_ever_comment = :id',
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
