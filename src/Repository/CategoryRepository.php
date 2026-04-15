<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function findByShopAndLanguage($shopId, $langId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('c, cl')
            ->from('PrestaShop\\Module\\Everpsblog\\Entity\\Category', 'c')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\CategoryLang', 'cl', 'WITH', 'cl.categoryId = c.id')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\CategoryShop', 'cs', 'WITH', 'cs.categoryId = c.id')
            ->where('cl.langId = :langId')
            ->andWhere('cs.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId)
            ->getQuery()
            ->getArrayResult();
    }
}
