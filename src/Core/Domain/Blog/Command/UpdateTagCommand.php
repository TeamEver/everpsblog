<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

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
