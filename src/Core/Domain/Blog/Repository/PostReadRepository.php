<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Everpsblog\Entity\Post;

class PostReadRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getById(int $postId): ?Post
    {
        /** @var Post|null $post */
        $post = $this->entityManager->getRepository(Post::class)->find($postId);

        return $post;
    }
}
