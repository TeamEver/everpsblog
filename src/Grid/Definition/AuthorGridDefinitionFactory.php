<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class AuthorGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'author',
            'Authors',
            [
                ['id' => 'id_ever_author', 'name' => 'ID'],
                ['id' => 'featured_image', 'name' => 'Image', 'type' => 'image'],
                ['id' => 'nickhandle', 'name' => 'Nickname'],
                ['id' => 'active', 'name' => 'Active'],
            ],
            [
                'q' => 'Search',
                'nickhandle' => 'Nickname',
            ],
            [
                ['id' => 'delete', 'name' => 'Delete selected'],
            ]
        );
    }
}
