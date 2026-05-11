<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject\PostCommandData;

if (!defined('_PS_VERSION_')) {
    exit;
}


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
