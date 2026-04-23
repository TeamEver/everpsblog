<?php

namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

final class CategoryGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'category',
            'Categories',
            [
                ['id' => 'id_ever_category', 'name' => 'ID'],
                ['id' => 'title', 'name' => 'Title'],
                ['id' => 'active', 'name' => 'Active'],
            ],
            [
                'q' => 'Search',
                'title' => 'Title',
            ],
            [
                ['id' => 'delete', 'name' => 'Delete selected'],
            ]
        );
    }
}
