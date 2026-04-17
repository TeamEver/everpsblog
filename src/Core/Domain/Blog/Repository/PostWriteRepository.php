<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Everpsblog\Entity\Post;

class PostWriteRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array<string, array<int, object>> $relations
     */
    public function save(Post $post, array $relations): void
    {
        $this->entityManager->wrapInTransaction(function () use ($post, $relations) {
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $postId = (int) $post->getId();
            $this->clearRelations($postId);
            $this->persistRelations($postId, $relations);

            $this->entityManager->flush();
        });
    }

    public function delete(Post $post): void
    {
        $this->entityManager->wrapInTransaction(function () use ($post) {
            $postId = (int) $post->getId();
            $this->clearRelations($postId);
            $this->entityManager->remove($post);
            $this->entityManager->flush();
        });
    }

    private function clearRelations(int $postId): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->delete('ever_blog_post_lang', ['id_ever_post' => $postId]);
        $connection->delete('ever_blog_post_shop', ['id_ever_post' => $postId]);
        $connection->delete('ever_blog_post_category', ['id_ever_post' => $postId]);
        $connection->delete('ever_blog_post_tag', ['id_ever_post' => $postId]);
        $connection->delete('ever_blog_post_product', ['id_ever_post' => $postId]);
    }

    /**
     * @param array<string, array<int, object>> $relations
     */
    private function persistRelations(int $postId, array $relations): void
    {
        foreach ($relations as $entities) {
            foreach ($entities as $entity) {
                if (method_exists($entity, 'setPostId')) {
                    $entity->setPostId($postId);
                }

                $this->entityManager->persist($entity);
            }
        }
    }
}
