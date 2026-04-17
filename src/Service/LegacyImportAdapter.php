<?php

namespace PrestaShop\Module\Everpsblog\Service;

class LegacyImportAdapter
{
    public function getOrCreateCategoryByLinkRewrite($linkRewrite)
    {
        $category = \EverPsBlogCategory::getCategoryByLinkRewrite((string) $linkRewrite);

        if (!\Validate::isLoadedObject($category)) {
            $category = new \EverPsBlogCategory();
        }

        return $category;
    }

    public function getOrCreateTagByLinkRewrite($linkRewrite)
    {
        $tag = \EverPsBlogTag::getTagByLinkRewrite((string) $linkRewrite);

        if (!\Validate::isLoadedObject($tag)) {
            $tag = new \EverPsBlogTag();
        }

        return $tag;
    }

    public function getOrCreateAuthorByNickhandle($nickhandle)
    {
        $author = \EverPsBlogAuthor::getAuthorByNickhandle((string) $nickhandle);

        if (!\Validate::isLoadedObject($author)) {
            $author = new \EverPsBlogAuthor();
            $author->nickhandle = (string) $nickhandle;
        }

        return $author;
    }

    public function getOrCreatePostByLinkRewrite($linkRewrite)
    {
        $post = \EverPsBlogPost::getPostByLinkRewrite((string) $linkRewrite);

        if (!\Validate::isLoadedObject($post)) {
            $post = new \EverPsBlogPost();
        }

        return $post;
    }

    public function getOrCreatePostImage($postId, $shopId)
    {
        $image = \EverPsBlogImage::getBlogImage((int) $postId, (int) $shopId, 'post');

        if (!\Validate::isLoadedObject($image)) {
            $image = new \EverPsBlogImage();
        }

        return $image;
    }
}
