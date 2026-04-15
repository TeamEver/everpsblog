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
            ['id' => 'id_ever_tag', 'name' => 'ID'],
            ['id' => 'title', 'name' => 'Titre'],
            ['id' => 'active', 'name' => 'Actif']
        ],
[
            'title' => 'Titre'
        ],
[
            ['id' => 'delete', 'name' => 'Supprimer sélection']
        ]
        );
    }
}
