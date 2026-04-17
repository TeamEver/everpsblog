<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class ImageRepository extends EntityRepository
{
    public function findOneForElement($elementId, $imageType)
    {
        return $this->createQueryBuilder('i')
            ->where('i.elementId = :elementId')
            ->andWhere('i.type = :type')
            ->setParameter('elementId', (int) $elementId)
            ->setParameter('type', (string) $imageType)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneForElementAndShop($elementId, $imageType, $shopId)
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.shops', 'is')
            ->where('i.elementId = :elementId')
            ->andWhere('i.type = :type')
            ->andWhere('is.shopId = :shopId')
            ->setParameter('elementId', (int) $elementId)
            ->setParameter('type', (string) $imageType)
            ->setParameter('shopId', (int) $shopId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
