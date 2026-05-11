<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Grid;

if (!defined('_PS_VERSION_')) {
    exit;
}


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
