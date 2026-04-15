<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class AuthorRepository extends EntityRepository
{
    public function findByShopAndLanguage($shopId, $langId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('a, al')
            ->from('PrestaShop\\Module\\Everpsblog\\Entity\\Author', 'a')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\AuthorLang', 'al', 'WITH', 'al.authorId = a.id')
            ->innerJoin('PrestaShop\\Module\\Everpsblog\\Entity\\AuthorShop', 'ash', 'WITH', 'ash.authorId = a.id')
            ->where('al.langId = :langId')
            ->andWhere('ash.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId)
            ->getQuery()
            ->getArrayResult();
    }
}
