<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateAuthorCommand;

class AuthorCommandAssembler
{
    /** @var AuthorRequestValidator */
    private $validator;

    /** @var int */
    private $shopId;

    public function __construct(AuthorRequestValidator $validator, int $shopId)
    {
        $this->validator = $validator;
        $this->shopId = $shopId;
    }

    public function assembleCreate(array $requestData): CreateAuthorCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new CreateAuthorCommand($this->mergeDefaults($validatedData));
    }

    public function assembleUpdate(int $authorId, array $requestData): UpdateAuthorCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new UpdateAuthorCommand($authorId, $this->mergeDefaults($validatedData));
    }

    private function mergeDefaults(array $data): array
    {
        return array_merge([
            'id_shop' => $this->shopId,
            'id_employee' => 0,
            'nickhandle' => '',
            'bio' => '',
            'meta_title' => '',
            'meta_description' => '',
            'twitter' => '',
            'facebook' => '',
            'linkedin' => '',
            'active' => 1,
            'indexable' => 1,
            'follow' => 1,
            'sitemap' => 1,
            'allowed_groups' => [],
            'author_products' => [],
        ], $data);
    }
}
