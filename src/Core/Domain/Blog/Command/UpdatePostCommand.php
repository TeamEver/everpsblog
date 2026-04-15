<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject\PostCommandData;

class UpdatePostCommand
{
    /** @var int */
    private $postId;
    /** @var PostCommandData */
    private $data;

    public function __construct(int $postId, PostCommandData $data)
    {
        $this->postId = $postId;
        $this->data = $data;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getData(): PostCommandData
    {
        return $this->data;
    }
}
