<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

use InvalidArgumentException;

class CommentRequestValidator
{
    public function validate(array $requestData): array
    {
        if (empty($requestData['id_ever_post'])) {
            throw new InvalidArgumentException('id_ever_post is required for comment commands.');
        }

        return $requestData;
    }
}
