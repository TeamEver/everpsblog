<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function findByShopAndLanguage($shopId, $langId)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('COALESCE(c.rootCategory, 0) = 0')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllCategories($langId, $shopId, $active = 1, $onlyParent = 0, $withoutParent = false)
    {
        $qb = $this->createLocalizedQb($shopId, $langId)
            ->andWhere('c.active = :active')
            ->setParameter('active', (int) $active)
            ->orderBy('cl.title', 'ASC');

        if ($onlyParent) {
            $qb->andWhere('c.parentId = :parentId')->setParameter('parentId', (int) $onlyParent);
        }

        if ($withoutParent) {
            $qb->andWhere('c.parentId IS NULL OR c.parentId = 0');
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function findParentCategories($categoryId, $langId, $shopId, $active = 1)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('c.active = :active')
            ->andWhere('c.id = :categoryId OR c.id = (SELECT c2.parentId FROM PrestaShop\\Module\\Everpsblog\\Entity\\Category c2 WHERE c2.id = :categoryId)')
            ->setParameter('active', (int) $active)
            ->setParameter('categoryId', (int) $categoryId)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findChildrenCategories($categoryId, $langId, $shopId, $active = 1)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('c.active = :active')
            ->andWhere('c.parentId = :categoryId')
            ->setParameter('active', (int) $active)
            ->setParameter('categoryId', (int) $categoryId)
            ->orderBy('cl.title', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findCategoryByLinkRewrite($linkRewrite, $langId, $shopId)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('cl.linkRewrite = :linkRewrite')
            ->setParameter('linkRewrite', (string) $linkRewrite)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createLocalizedQb($shopId, $langId)
    {
        return $this->createQueryBuilder('c')
            ->addSelect('cl')
            ->innerJoin('c.translations', 'cl')
            ->innerJoin('c.shops', 'cs')
            ->andWhere('cl.langId = :langId')
            ->andWhere('cs.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId);
    }
}
