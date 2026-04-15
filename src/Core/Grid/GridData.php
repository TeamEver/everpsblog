<?php

namespace PrestaShop\Module\Everpsblog\Core\Grid;

final class GridData
{
    /** @var array<int, array<string, mixed>> */
    private $records;

    /** @param array<int, array<string, mixed>> $records */
    public function __construct(array $records)
    {
        $this->records = $records;
    }

    /** @return array<int, array<string, mixed>> */
    public function getRecords(): array
    {
        return $this->records;
    }
}
