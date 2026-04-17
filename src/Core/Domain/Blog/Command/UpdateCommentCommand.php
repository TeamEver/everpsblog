<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

class UpdateCommentCommand
{
    /** @var int */
    private $commentId;

    /** @var array<string, mixed> */
    private $data;

    public function __construct(int $commentId, array $data)
    {
        $this->commentId = $commentId;
        $this->data = $data;
    }

    public function getCommentId(): int
    {
        return $this->commentId;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
