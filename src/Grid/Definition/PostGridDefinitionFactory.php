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
            ['id' => 'id_ever_post', 'name' => 'ID'],
            ['id' => 'title', 'name' => 'Titre'],
            ['id' => 'post_status', 'name' => 'Statut'],
            ['id' => 'count', 'name' => 'Vues']
        ],
[
            'title' => 'Titre',
            'post_status' => 'Statut'
        ],
[
            ['id' => 'delete', 'name' => 'Supprimer sélection'],
            ['id' => 'publishall', 'name' => 'Publier sélection']
        ]
        );
    }
}
