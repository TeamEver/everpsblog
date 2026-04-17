<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{
    public function findByPostAndLanguage($postId, $langId)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.post', 'p')
            ->where('p.id = :postId')
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
            ->innerJoin('c.post', 'p')
            ->where('p.id = :postId')
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
            ->innerJoin('c.post', 'p')
            ->where('p.id = :postId')
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
}
