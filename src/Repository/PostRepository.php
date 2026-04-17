<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
    public function findBackOfficeList($langId, $shopId, $limit = 50)
    {
        return $this->createBasePublishedQb($langId, $shopId, null)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findPostsForChoices($langId, $shopId, $limit = 500, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('p.id, pl.title')
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findLatestPosts($langId, $shopId, $start = 0, $limit = 10, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findStarredPosts($langId, $shopId, $start = 0, $limit = 10, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->andWhere('p.starred = 1')
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findPostsByTag($tagId, $langId, $shopId, $start = 0, $limit = 10, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->innerJoin('p.postTags', 'pt')
            ->innerJoin('pt.tag', 't')
            ->andWhere('t.id = :tagId')
            ->setParameter('tagId', (int) $tagId)
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findPostsByCategory($categoryId, $langId, $shopId, $start = 0, $limit = 10, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->innerJoin('p.postCategories', 'pc')
            ->innerJoin('pc.category', 'c')
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', (int) $categoryId)
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findPostsByAuthor($authorId, $langId, $shopId, $start = 0, $limit = 10, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->innerJoin('p.author', 'a')
            ->andWhere('a.id = :authorId')
            ->setParameter('authorId', (int) $authorId)
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findPostsByProduct($productId, $langId, $shopId, $start = 0, $limit = 10, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->innerJoin('p.postProducts', 'pp')
            ->andWhere('pp.productId = :productId')
            ->setParameter('productId', (int) $productId)
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findPostByLinkRewrite($linkRewrite, $langId, $shopId, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->andWhere('pl.linkRewrite = :linkRewrite')
            ->setParameter('linkRewrite', (string) $linkRewrite)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function searchPosts($query, $langId, $shopId, $start = 0, $limit = 10, $status = 'published')
    {
        return $this->createBasePublishedQb($langId, $shopId, $status)
            ->andWhere('pl.title LIKE :search OR pl.content LIKE :search OR pl.excerpt LIKE :search')
            ->setParameter('search', '%' . $query . '%')
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function countPublishedPosts($langId, $shopId, $status = 'published')
    {
        return (int) $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('COUNT(DISTINCT p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPostsByTag($tagId, $langId, $shopId, $status = 'published')
    {
        return (int) $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('COUNT(DISTINCT p.id)')
            ->innerJoin('p.postTags', 'pt')
            ->innerJoin('pt.tag', 't')
            ->andWhere('t.id = :tagId')
            ->setParameter('tagId', (int) $tagId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPostsByCategory($categoryId, $langId, $shopId, $status = 'published')
    {
        return (int) $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('COUNT(DISTINCT p.id)')
            ->innerJoin('p.postCategories', 'pc')
            ->innerJoin('pc.category', 'c')
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', (int) $categoryId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPostsByAuthor($authorId, $langId, $shopId, $status = 'published')
    {
        return (int) $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('COUNT(DISTINCT p.id)')
            ->innerJoin('p.author', 'a')
            ->andWhere('a.id = :authorId')
            ->setParameter('authorId', (int) $authorId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPostsBySearch($query, $langId, $shopId, $status = 'published')
    {
        return (int) $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('COUNT(DISTINCT p.id)')
            ->andWhere('pl.title LIKE :search OR pl.content LIKE :search OR pl.excerpt LIKE :search')
            ->setParameter('search', '%' . $query . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function incrementPostViewCount($postId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->update('PrestaShop\\Module\\Everpsblog\\Entity\\Post', 'p')
            ->set('p.viewCount', 'p.viewCount + 1')
            ->where('p.id = :postId')
            ->setParameter('postId', (int) $postId)
            ->getQuery()
            ->execute();
    }

    private function createBasePublishedQb($langId, $shopId, $status)
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.translations', 'pl')
            ->innerJoin('p.shops', 'ps')
            ->andWhere('pl.langId = :langId')
            ->andWhere('ps.shopId = :shopId')
            ->setParameter('langId', (int) $langId)
            ->setParameter('shopId', (int) $shopId);

        if (null !== $status) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', (string) $status);
        }

        return $qb;
    }
}
