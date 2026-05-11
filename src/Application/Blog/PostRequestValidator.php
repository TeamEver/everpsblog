<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Application\Blog;

if (!defined('_PS_VERSION_')) {
    exit;
}


class PostRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $this->ensureDefaultTitle($requestData);
        $requestData = $this->normalizeSeoFields($requestData);
        $requestData = $this->normalizePostStatusAndDate($requestData);

        $authorId = (int) ($requestData['id_author'] ?? 0);
        if ($authorId > 0 && !$this->existsInCurrentShopModuleTable('ever_blog_author', 'id_ever_author', $authorId, 'ever_blog_author_shop')) {
            $this->addFieldError('id_author', $this->transAdmin('Author not found (id: %id%).', ['%id%' => $authorId]));
        }

        $defaultCategoryId = (int) ($requestData['id_default_category'] ?? 0);
        if ($defaultCategoryId > 0 && !$this->existsInCurrentShopModuleTable('ever_blog_category', 'id_ever_category', $defaultCategoryId, 'ever_blog_category_shop')) {
            $this->addFieldError('id_default_category', $this->transAdmin('Category not found (id: %id%).', ['%id%' => $defaultCategoryId]));
        }

        $postCategories = $this->normalizeIntCollection($requestData['post_categories'] ?? []);
        foreach ($postCategories as $categoryId) {
            if (!$this->existsInCurrentShopModuleTable('ever_blog_category', 'id_ever_category', $categoryId, 'ever_blog_category_shop')) {
                $this->addFieldError('post_categories', $this->transAdmin('Category not found (id: %id%).', ['%id%' => $categoryId]));
            }
        }
        $requestData['post_categories'] = $postCategories;

        $postTags = $this->normalizeIntCollection($requestData['post_tags'] ?? []);
        foreach ($postTags as $tagId) {
            if (!$this->existsInCurrentShopModuleTable('ever_blog_tag', 'id_ever_tag', $tagId, 'ever_blog_tag_shop')) {
                $this->addFieldError('post_tags', $this->transAdmin('Tag not found (id: %id%).', ['%id%' => $tagId]));
            }
        }
        $requestData['post_tags'] = $postTags;

        $postProducts = $this->normalizeIntCollection($requestData['post_products'] ?? []);
        foreach ($postProducts as $productId) {
            if (!$this->existsInCurrentShopPrestashopTable('product', 'id_product', $productId, 'product_shop')) {
                $this->addFieldError('post_products', $this->transAdmin('Product not found (id: %id%).', ['%id%' => $productId]));
            }
        }
        $requestData['post_products'] = $postProducts;

        $this->throwIfInvalid();

        return $requestData;
    }
}
