<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

class UpdateAuthorCommand
{
    /** @var int */
    private $authorId;

    /** @var array<string, mixed> */
    private $data;

    public function __construct(int $authorId, array $data)
    {
        $this->authorId = $authorId;
        $this->data = $data;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
