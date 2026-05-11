<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Application\Blog;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateCommentCommand;

if (!defined('_PS_VERSION_')) {
    exit;
}


class CommentCommandAssembler
{
    /** @var CommentRequestValidator */
    private $validator;

    public function __construct(CommentRequestValidator $validator)
    {
        $this->validator = $validator;
    }

    public function assembleCreate(array $requestData): CreateCommentCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new CreateCommentCommand($this->mergeDefaults($validatedData));
    }

    public function assembleUpdate(int $commentId, array $requestData): UpdateCommentCommand
    {
        $validatedData = $this->validator->validate($requestData);

        return new UpdateCommentCommand($commentId, $this->mergeDefaults($validatedData));
    }

    private function mergeDefaults(array $data): array
    {
        return array_merge([
            'id_lang' => 0,
            'comment' => (string) ($data['content'] ?? ''),
            'name' => (string) ($data['nickname'] ?? ''),
            'user_email' => '',
            'active' => 1,
        ], $data);
    }
}
