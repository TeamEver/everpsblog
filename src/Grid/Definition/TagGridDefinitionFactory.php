<?php

namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

final class TagGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'tag',
            'Tags',
            [
                ['id' => 'id_ever_tag', 'name' => '#', 'type' => 'id'],
                ['id' => 'title', 'name' => 'Title', 'type' => 'text'],
                ['id' => 'link_rewrite', 'name' => 'Slug', 'type' => 'text'],
                ['id' => 'count', 'name' => 'Views', 'type' => 'number'],
                ['id' => 'active', 'name' => 'Active', 'type' => 'status'],
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
