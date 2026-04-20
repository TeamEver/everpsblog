<?php

namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

final class PostGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'post',
            'Articles',
            [
                ['id' => 'id_ever_post', 'name' => '#', 'type' => 'id'],
                ['id' => 'featured_image', 'name' => 'Image', 'type' => 'image'],
                ['id' => 'title', 'name' => 'Titre', 'type' => 'text'],
                ['id' => 'post_status', 'name' => 'Statut', 'type' => 'status'],
                ['id' => 'count', 'name' => 'Vues', 'type' => 'number'],
            ],
            [
                'title' => 'Titre',
                'post_status' => 'Statut',
            ],
            [
                ['id' => 'delete', 'name' => 'Supprimer la sélection'],
                ['id' => 'publishall', 'name' => 'Publier la sélection'],
            ]
        );
    }
}
