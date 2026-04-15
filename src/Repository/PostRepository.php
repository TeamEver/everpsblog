<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function findBackOfficeList($langId, $shopId, $limit = 50)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p, pl')
            ->from('PrestaShop\\Module\\Everpsblog\\Entity\\Post', 'p')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\PostLang', 'pl', 'WITH', 'pl.postId = p.id')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\PostShop', 'ps', 'WITH', 'ps.postId = p.id')
            ->where('pl.langId = :langId')
            ->andWhere('ps.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId)
            ->setMaxResults((int) $limit)
            ->getQuery()
            ->getArrayResult();
    }
}
