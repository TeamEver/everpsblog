<?php

namespace PrestaShop\Module\Everpsblog\Core\Grid;

final class GridDefinition
{
    /** @var string */
    private $id;
    /** @var string */
    private $title;
    /** @var array<int, array<string, string>> */
    private $columns;
    /** @var array<string, string> */
    private $filters;
    /** @var array<int, array<string, string>> */
    private $bulkActions;

    /**
     * @param array<int, array<string, string>> $columns
     * @param array<string, string> $filters
     * @param array<int, array<string, string>> $bulkActions
     */
    public function __construct(string $id, string $title, array $columns, array $filters = [], array $bulkActions = [])
    {
        $this->id = $id;
        $this->title = $title;
        $this->columns = $columns;
        $this->filters = $filters;
        $this->bulkActions = $bulkActions;
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getColumns(): array { return $this->columns; }
    public function getFilters(): array { return $this->filters; }
    public function getBulkActions(): array { return $this->bulkActions; }
}
