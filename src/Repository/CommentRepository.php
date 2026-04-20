<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{
    public function findByPostAndLanguage($postId, $langId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.postId = :postId')
            ->andWhere('c.langId = :langId')
            ->setParameter('postId', (int) $postId)
            ->setParameter('langId', (int) $langId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findCommentsByPost($postId, $langId, $active = 1)
    {
        return $this->createQueryBuilder('c')
            ->where('c.postId = :postId')
            ->andWhere('c.langId = :langId')
            ->andWhere('c.active = :active')
            ->setParameter('postId', (int) $postId)
            ->setParameter('langId', (int) $langId)
            ->setParameter('active', (int) $active)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findCommentsByEmail($email, $langId, $active = 1)
    {
        return $this->createQueryBuilder('c')
            ->where('c.userEmail = :email')
            ->andWhere('c.langId = :langId')
            ->andWhere('c.active = :active')
            ->setParameter('email', (string) $email)
            ->setParameter('langId', (int) $langId)
            ->setParameter('active', (int) $active)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function countCommentsByPost($postId, $langId, $active = 1)
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.postId = :postId')
            ->andWhere('c.langId = :langId')
            ->andWhere('c.active = :active')
            ->setParameter('postId', (int) $postId)
            ->setParameter('langId', (int) $langId)
            ->setParameter('active', (int) $active)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLatestCommentByEmail($email, $langId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.userEmail = :email')
            ->andWhere('c.langId = :langId')
            ->setParameter('email', (string) $email)
            ->setParameter('langId', (int) $langId)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Back office listing for the comments grid.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findBackOfficeList($langId, $limit = 100)
    {
        return $this->createQueryBuilder('c')
            ->select('c.id AS id_ever_comment, c.postId AS id_ever_post, c.name AS name, c.userEmail AS user_email, c.comment AS comment, c.active AS active, c.createdAt AS date_add')
            ->where('c.langId = :langId')
            ->setParameter('langId', (int) $langId)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults((int) $limit)
            ->getQuery()
            ->getArrayResult();
    }
}
