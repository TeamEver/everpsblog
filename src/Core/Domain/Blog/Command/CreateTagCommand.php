<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command;

class CreateTagCommand
{
    /** @var array<string, mixed> */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
