<?php

namespace PrestaShop\Module\Everpsblog\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use PrestaShop\Module\Everpsblog\Entity\Category;
use PrestaShop\Module\Everpsblog\Entity\Post;
use PrestaShop\Module\Everpsblog\Entity\PostCategory;
use PrestaShop\Module\Everpsblog\Entity\PostLang;
use PrestaShop\Module\Everpsblog\Entity\PostProduct;
use PrestaShop\Module\Everpsblog\Entity\PostShop;
use PrestaShop\Module\Everpsblog\Entity\PostTag;
use PrestaShop\Module\Everpsblog\Entity\Tag;

class PostRepository extends EntityRepository
{
    public function findBackOfficeList($langId, $shopId, $limit = 50)
    {
        return $this->createBasePublishedQb($langId, $shopId, null)
            ->select('p.id AS id_ever_post, p.status AS post_status, p.viewCount AS count, pl.title AS title')
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
            ->innerJoin(PostTag::class, 'pt', Join::WITH, 'pt.postId = p.id')
            ->innerJoin(Tag::class, 't', Join::WITH, 't.id = pt.tagId')
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
            ->innerJoin(PostCategory::class, 'pc', Join::WITH, 'pc.postId = p.id')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id = pc.categoryId')
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
            ->andWhere('p.authorId = :authorId')
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
            ->innerJoin(PostProduct::class, 'pp', Join::WITH, 'pp.postId = p.id')
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
            ->innerJoin(PostTag::class, 'pt', Join::WITH, 'pt.postId = p.id')
            ->innerJoin(Tag::class, 't', Join::WITH, 't.id = pt.tagId')
            ->andWhere('t.id = :tagId')
            ->setParameter('tagId', (int) $tagId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPostsByCategory($categoryId, $langId, $shopId, $status = 'published')
    {
        return (int) $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('COUNT(DISTINCT p.id)')
            ->innerJoin(PostCategory::class, 'pc', Join::WITH, 'pc.postId = p.id')
            ->innerJoin(Category::class, 'c', Join::WITH, 'c.id = pc.categoryId')
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', (int) $categoryId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPostsByAuthor($authorId, $langId, $shopId, $status = 'published')
    {
        return (int) $this->createBasePublishedQb($langId, $shopId, $status)
            ->select('COUNT(DISTINCT p.id)')
            ->andWhere('p.authorId = :authorId')
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
        return $this->_em->createQueryBuilder()
            ->update(Post::class, 'p')
            ->set('p.viewCount', 'p.viewCount + 1')
            ->where('p.id = :postId')
            ->setParameter('postId', (int) $postId)
            ->getQuery()
            ->execute();
    }

    private function createBasePublishedQb($langId, $shopId, $status)
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin(PostLang::class, 'pl', Join::WITH, 'pl.postId = p.id')
            ->innerJoin(PostShop::class, 'ps', Join::WITH, 'ps.postId = p.id')
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

    public function getPosts($idLang, $idShop, $offset = 0, $limit = null, $status = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.shopId = :shop')
            ->setParameter('shop', $idShop);

        if ($status) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status);
        }

        $qb->orderBy('p.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit)
               ->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }
}
