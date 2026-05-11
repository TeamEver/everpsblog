<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Application\Blog;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateTagCommand;

if (!defined('_PS_VERSION_')) {
    exit;
}


class TagCommandAssembler
{
    /** @var TagRequestValidator */
    private $validator;

    /** @var int */
    private $shopId;

    public function __construct(TagRequestValidator $validator, int $shopId)
    {
        $this->validator = $validator;
        $this->shopId = $shopId;
    }

    public function assembleCreate(array $requestData): CreateTagCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new CreateTagCommand($this->mergeDefaults($validatedData));
    }

    public function assembleUpdate(int $tagId, array $requestData): UpdateTagCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new UpdateTagCommand($tagId, $this->mergeDefaults($validatedData));
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
            'allowed_groups' => [],
            'tag_products' => [],
        ], $data);
    }
}
