<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

if (!defined('_PS_VERSION_')) {
    exit;
}


class UpdateTagCommand
{
    /** @var int */
    private $tagId;

    /** @var array<string, mixed> */
    private $data;

    public function __construct(int $tagId, array $data)
    {
        $this->tagId = $tagId;
        $this->data = $data;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
