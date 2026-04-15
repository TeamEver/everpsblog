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
            ->getQuery()
            ->getResult();
    }
}
