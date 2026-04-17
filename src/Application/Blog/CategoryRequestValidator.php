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
        if ($parentCategoryId > 0 && !$this->existsInModuleTable('ever_blog_category', 'id_ever_category', $parentCategoryId)) {
            $this->addFieldError('id_parent_category', sprintf('Catégorie parente introuvable (id: %d).', $parentCategoryId));
        }

        $this->throwIfInvalid();

        return $requestData;
    }
}
