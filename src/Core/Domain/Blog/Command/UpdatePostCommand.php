<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject\PostCommandData;

if (!defined('_PS_VERSION_')) {
    exit;
}


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
