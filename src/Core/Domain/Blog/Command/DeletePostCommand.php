<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

class DeletePostCommand
{
    /** @var int */
    private $postId;

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }
}
