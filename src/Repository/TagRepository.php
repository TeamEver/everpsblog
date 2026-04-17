<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    public function findByShopAndLanguage($shopId, $langId)
    {
        return $this->createLocalizedQb($shopId, $langId)->getQuery()->getArrayResult();
    }

    public function findAllTags($langId, $shopId, $active = 1)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('t.active = :active')
            ->setParameter('active', (int) $active)
            ->orderBy('tl.title', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findTagByLinkRewrite($linkRewrite, $langId, $shopId)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('tl.linkRewrite = :linkRewrite')
            ->setParameter('linkRewrite', (string) $linkRewrite)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createLocalizedQb($shopId, $langId)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.translations', 'tl')
            ->innerJoin('t.shops', 'ts')
            ->andWhere('tl.langId = :langId')
            ->andWhere('ts.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId);
    }
}
