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

    /**
     * @return array<int, string>
     */
    public function getLocalizedSlugs(int $postId): array
    {
        if ($postId <= 0) {
            return [];
        }

        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT `id_lang`, `link_rewrite`
             FROM `' . _DB_PREFIX_ . 'ever_blog_post_lang`
             WHERE `id_ever_post` = ' . (int) $postId
        );

        if (!is_array($rows)) {
            return [];
        }

        $slugs = [];
        foreach ($rows as $row) {
            $langId = (int) ($row['id_lang'] ?? 0);
            $slug = trim((string) ($row['link_rewrite'] ?? ''));

            if ($langId <= 0 || '' === $slug) {
                continue;
            }

            $slugs[$langId] = $slug;
        }

        return $slugs;
    }

    private function clearRelations(int $postId): void
    {
        if ($postId <= 0) {
            return;
        }

        $connection = $this->entityManager->getConnection();
        $connection->delete(_DB_PREFIX_ . 'ever_blog_post_lang', ['id_ever_post' => $postId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_post_shop', ['id_ever_post' => $postId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_post_category', ['id_ever_post' => $postId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_post_tag', ['id_ever_post' => $postId]);
        $connection->delete(_DB_PREFIX_ . 'ever_blog_post_product', ['id_ever_post' => $postId]);
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
