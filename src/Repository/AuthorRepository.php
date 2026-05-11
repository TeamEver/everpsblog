<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}


class AuthorRepository extends EntityRepository
{
    public function findByShopAndLanguage($shopId, $langId)
    {
        return $this->createLocalizedQb($shopId, $langId)->getQuery()->getArrayResult();
    }

    public function findAllAuthors($langId, $shopId, $active = 1)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('a.active = :active')
            ->setParameter('active', (int) $active)
            ->orderBy('a.nickhandle', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAuthorByNickhandle($nickhandle, $langId, $shopId)
    {
        return $this->createLocalizedQb($shopId, $langId)
            ->andWhere('a.nickhandle = :nickhandle')
            ->setParameter('nickhandle', (string) $nickhandle)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createLocalizedQb($shopId, $langId)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.translations', 'al')
            ->innerJoin('a.shops', 'ash')
            ->andWhere('al.langId = :langId')
            ->andWhere('ash.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId);
    }
}
