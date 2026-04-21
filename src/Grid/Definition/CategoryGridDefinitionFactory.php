<?php

namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

final class CategoryGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'category',
            'Categorys',
[
            ['id' => 'id_ever_category', 'name' => 'ID'],
            ['id' => 'title', 'name' => 'Titre'],
            ['id' => 'active', 'name' => 'Actif']
        ],
[
            'q' => 'Recherche',
            'title' => 'Titre'
        ],
[
            ['id' => 'delete', 'name' => 'Supprimer sélection']
        ]
        );
    }
}
