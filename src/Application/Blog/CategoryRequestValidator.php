<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class CategoryRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $this->ensureDefaultTitle($requestData);
        $requestData = $this->normalizeSeoFields($requestData);

        $parentCategoryId = (int) ($requestData['id_parent_category'] ?? 0);
        if ($parentCategoryId > 0 && !$this->existsInCurrentShopModuleTable('ever_blog_category', 'id_ever_category', $parentCategoryId, 'ever_blog_category_shop')) {
            $this->addFieldError('id_parent_category', $this->transAdmin('Parent category not found (id: %id%).', ['%id%' => $parentCategoryId]));
        }

        $categoryProducts = $this->normalizeIntCollection($requestData['category_products'] ?? []);
        foreach ($categoryProducts as $productId) {
            if (!$this->existsInCurrentShopPrestashopTable('product', 'id_product', $productId, 'product_shop')) {
                $this->addFieldError('category_products', $this->transAdmin('Product not found (id: %id%).', ['%id%' => $productId]));
            }
        }
        $requestData['category_products'] = $categoryProducts;

        $this->throwIfInvalid();

        return $requestData;
    }
}
