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
}
