<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler\PostCommandDataBuilder;

class PostCommandAssembler
{
    /** @var PostCommandDataBuilder */
    private $dataBuilder;

    /** @var PostRequestValidator */
    private $validator;

    public function __construct(PostCommandDataBuilder $dataBuilder, PostRequestValidator $validator)
    {
        $this->dataBuilder = $dataBuilder;
        $this->validator = $validator;
    }

    public function assembleCreate(array $requestData): CreatePostCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new CreatePostCommand($this->dataBuilder->buildFromRequestData($validatedData));
    }

    public function assembleUpdate(int $postId, array $requestData): UpdatePostCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new UpdatePostCommand($postId, $this->dataBuilder->buildFromRequestData($validatedData));
    }
}
