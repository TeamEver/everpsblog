<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class AuthorRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $nickhandle = trim((string) ($requestData['nickhandle'] ?? ''));
        if ('' === $nickhandle) {
            $this->addFieldError('nickhandle', $this->transAdmin('Nickname is required.'));
        }

        $requestData = $this->normalizeSeoFields($requestData, 'meta_title_');

        $employeeId = (int) ($requestData['id_employee'] ?? 0);
        if ($employeeId > 0 && !$this->existsInPrestashopTable('employee', 'id_employee', $employeeId)) {
            $this->addFieldError('id_employee', $this->transAdmin('Employee not found (id: %id%).', ['%id%' => $employeeId]));
        }

        $authorProducts = $this->normalizeIntCollection($requestData['author_products'] ?? []);
        foreach ($authorProducts as $productId) {
            if (!$this->existsInCurrentShopPrestashopTable('product', 'id_product', $productId, 'product_shop')) {
                $this->addFieldError('author_products', $this->transAdmin('Product not found (id: %id%).', ['%id%' => $productId]));
            }
        }
        $requestData['author_products'] = $authorProducts;

        $this->throwIfInvalid();

        return $requestData;
    }
}
