<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class PostRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $this->ensureDefaultTitle($requestData);
        $requestData = $this->normalizeSeoFields($requestData);
        $requestData = $this->normalizePostStatusAndDate($requestData);

        $authorId = (int) ($requestData['id_author'] ?? 0);
        if ($authorId > 0 && !$this->existsInModuleTable('ever_blog_author', 'id_ever_author', $authorId)) {
            $this->addFieldError('id_author', sprintf('Auteur introuvable (id: %d).', $authorId));
        }

        $defaultCategoryId = (int) ($requestData['id_default_category'] ?? 0);
        if ($defaultCategoryId > 0 && !$this->existsInModuleTable('ever_blog_category', 'id_ever_category', $defaultCategoryId)) {
            $this->addFieldError('id_default_category', sprintf('Catégorie introuvable (id: %d).', $defaultCategoryId));
        }

        $postCategories = $this->normalizeIntCollection($requestData['post_categories'] ?? []);
        foreach ($postCategories as $categoryId) {
            if (!$this->existsInModuleTable('ever_blog_category', 'id_ever_category', $categoryId)) {
                $this->addFieldError('post_categories', sprintf('Catégorie introuvable (id: %d).', $categoryId));
            }
        }
        $requestData['post_categories'] = $postCategories;

        $postTags = $this->normalizeIntCollection($requestData['post_tags'] ?? []);
        foreach ($postTags as $tagId) {
            if (!$this->existsInModuleTable('ever_blog_tag', 'id_ever_tag', $tagId)) {
                $this->addFieldError('post_tags', sprintf('Tag introuvable (id: %d).', $tagId));
            }
        }
        $requestData['post_tags'] = $postTags;

        $postProducts = $this->normalizeIntCollection($requestData['post_products'] ?? []);
        foreach ($postProducts as $productId) {
            if (!$this->existsInPrestashopTable('product', 'id_product', $productId)) {
                $this->addFieldError('post_products', sprintf('Produit introuvable (id: %d).', $productId));
            }
        }
        $requestData['post_products'] = $postProducts;

        $this->throwIfInvalid();

        return $requestData;
    }
}
