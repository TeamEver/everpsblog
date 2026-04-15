<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

final class AuthorFormDataProvider
{
    /**
     * @return array<string, mixed>
     */
    public function getData(?int $id = null): array
    {
        return [
            'id' => $id,
            'nickhandle' => '',
            'bio' => '',
        ];
    }
}
