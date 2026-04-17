<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

class DeleteCategoryCommand
{
    /** @var int */
    private $categoryId;

    public function __construct(int $categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
}
