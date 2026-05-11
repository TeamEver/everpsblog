<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

if (!defined('_PS_VERSION_')) {
    exit;
}


class UpdateCategoryCommand
{
    /** @var int */
    private $categoryId;

    /** @var array<string, mixed> */
    private $data;

    public function __construct(int $categoryId, array $data)
    {
        $this->categoryId = $categoryId;
        $this->data = $data;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
