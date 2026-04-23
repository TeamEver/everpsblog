<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class TagRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $this->ensureDefaultTitle($requestData);
        $requestData = $this->normalizeSeoFields($requestData);

        $tagProducts = $this->normalizeIntCollection($requestData['tag_products'] ?? []);
        foreach ($tagProducts as $productId) {
            if (!$this->existsInCurrentShopPrestashopTable('product', 'id_product', $productId, 'product_shop')) {
                $this->addFieldError('tag_products', $this->transAdmin('Product not found (id: %id%).', ['%id%' => $productId]));
            }
        }
        $requestData['tag_products'] = $tagProducts;

        $this->throwIfInvalid();

        return $requestData;
    }
}
