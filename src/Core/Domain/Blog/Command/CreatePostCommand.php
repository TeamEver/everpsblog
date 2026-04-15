<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject\PostCommandData;

class CreatePostCommand
{
    /** @var PostCommandData */
    private $data;

    public function __construct(PostCommandData $data)
    {
        $this->data = $data;
    }

    public function getData(): PostCommandData
    {
        return $this->data;
    }
}
