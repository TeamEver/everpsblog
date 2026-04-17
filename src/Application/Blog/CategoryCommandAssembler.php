<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateCategoryCommand;

class CategoryCommandAssembler
{
    /** @var CategoryRequestValidator */
    private $validator;

    /** @var int */
    private $shopId;

    public function __construct(CategoryRequestValidator $validator, int $shopId)
    {
        $this->validator = $validator;
        $this->shopId = $shopId;
    }

    public function assembleCreate(array $requestData): CreateCategoryCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new CreateCategoryCommand($this->mergeDefaults($validatedData));
    }

    public function assembleUpdate(int $categoryId, array $requestData): UpdateCategoryCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new UpdateCategoryCommand($categoryId, $this->mergeDefaults($validatedData));
    }

    private function mergeDefaults(array $data): array
    {
        return array_merge([
            'id_shop' => $this->shopId,
            'title' => '',
            'meta_title' => '',
            'meta_description' => '',
            'content' => '',
            'bottom_content' => '',
            'active' => 1,
            'indexable' => 1,
            'follow' => 1,
            'sitemap' => 1,
            'id_parent_category' => null,
            'is_root_category' => 0,
        ], $data);
    }
}
