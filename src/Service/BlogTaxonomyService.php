<?php

namespace PrestaShop\Module\Everpsblog\Service;

class BlogTaxonomyService
{
    public function insert($idElement, $idPost, $taxonomy)
    {
        return \EverPsBlogTaxonomy::insertTaxonomy((int) $idElement, (int) $idPost, $taxonomy);
    }

    public function dropPostTaxonomies($postId)
    {
        \EverPsBlogTaxonomy::dropTaxonomy((int) $postId, 'category');
        \EverPsBlogTaxonomy::dropTaxonomy((int) $postId, 'tag');

        return \EverPsBlogTaxonomy::dropTaxonomy((int) $postId, 'product');
    }

    public function dropCategoryTaxonomy($categoryId)
    {
        return \EverPsBlogTaxonomy::dropCategoryTaxonomy((int) $categoryId);
    }

    public function dropTagTaxonomy($tagId)
    {
        return \EverPsBlogTaxonomy::dropTagTaxonomy((int) $tagId);
    }

    public function dropProductTaxonomy($productId)
    {
        return \EverPsBlogTaxonomy::dropProductTaxonomy((int) $productId);
    }

    public function getPostProductsTaxonomies($postId)
    {
        return \EverPsBlogTaxonomy::getPostProductsTaxonomies((int) $postId);
    }

    public function checkDefaultPostCategory($postId)
    {
        return \EverPsBlogTaxonomy::checkDefaultPostCategory((int) $postId);
    }
}
