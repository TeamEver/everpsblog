<?php

namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

final class AuthorGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'author',
            'Authors',
[
            ['id' => 'id_ever_author', 'name' => 'ID'],
            ['id' => 'nickhandle', 'name' => 'Pseudo'],
            ['id' => 'active', 'name' => 'Actif']
        ],
[
            'nickhandle' => 'Pseudo'
        ],
[
            ['id' => 'delete', 'name' => 'Supprimer sélection']
        ]
        );
    }
}
