<?php

namespace PrestaShop\Module\Everpsblog\Grid\Definition;

use PrestaShop\Module\Everpsblog\Core\Grid\GridDefinition;

final class CommentGridDefinitionFactory
{
    public function build(): GridDefinition
    {
        return new GridDefinition(
            'comment',
            'Comments',
            [
                ['id' => 'id_ever_comment', 'name' => 'ID'],
                ['id' => 'id_ever_post', 'name' => 'Post ID'],
                ['id' => 'active', 'name' => 'Active'],
            ],
            [
                'q' => 'Search',
                'id_ever_post' => 'Post ID',
            ],
            [
                ['id' => 'delete', 'name' => 'Delete selected'],
                ['id' => 'approveall', 'name' => 'Approve selected'],
            ]
        );
    }
}
