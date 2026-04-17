<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

class DeleteAuthorCommand
{
    /** @var int */
    private $authorId;

    public function __construct(int $authorId)
    {
        $this->authorId = $authorId;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }
}
