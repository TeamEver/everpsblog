<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

class DeleteAuthorCommand
{
    /** @var int */
    private $authorId;
    /** @var int|null */
    private $reassignToAuthorId;

    public function __construct(int $authorId, ?int $reassignToAuthorId = null)
    {
        $this->authorId = $authorId;
        $this->reassignToAuthorId = ($reassignToAuthorId !== null && $reassignToAuthorId > 0) ? $reassignToAuthorId : null;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function getReassignToAuthorId(): ?int
    {
        return $this->reassignToAuthorId;
    }
}
