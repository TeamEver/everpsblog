<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    public function findByShopAndLanguage($shopId, $langId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('t, tl')
            ->from('PrestaShop\\Module\\Everpsblog\\Entity\\Tag', 't')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\TagLang', 'tl', 'WITH', 'tl.tagId = t.id')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\TagShop', 'ts', 'WITH', 'ts.tagId = t.id')
            ->where('tl.langId = :langId')
            ->andWhere('ts.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId)
            ->getQuery()
            ->getArrayResult();
    }
}
