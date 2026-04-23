<?php

namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

final class PostGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'post',
            'Posts',
            [
                ['id' => 'id_ever_post', 'name' => '#', 'type' => 'id', 'sortable' => true],
                ['id' => 'featured_image', 'name' => 'Image', 'type' => 'image'],
                ['id' => 'title', 'name' => 'Title', 'type' => 'text', 'sortable' => true],
                ['id' => 'post_status', 'name' => 'Status', 'type' => 'status', 'sortable' => true],
                ['id' => 'date_add', 'name' => 'Publication date', 'type' => 'date'],
                ['id' => 'count', 'name' => 'Views', 'type' => 'number', 'sortable' => true],
            ],
            [
                'q' => 'Search',
                'title' => 'Title',
                'post_status' => 'Status',
            ],
            [
                ['id' => 'delete', 'name' => 'Delete selected'],
                ['id' => 'publishall', 'name' => 'Publish selected'],
                ['id' => 'duplicate', 'name' => 'Duplicate selected'],
            ]
        );
    }
}
