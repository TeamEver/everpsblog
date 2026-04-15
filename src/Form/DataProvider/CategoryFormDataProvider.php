<?php

namespace PrestaShop\Module\Everpsblog\Form\DataProvider;

final class CategoryFormDataProvider
{
    /**
     * @return array<string, mixed>
     */
    public function getData(?int $id = null): array
    {
        return [
            'id' => $id,
            'title' => '',
            'meta_title' => '',
        ];
    }
}
