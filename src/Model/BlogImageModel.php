<?php

namespace PrestaShop\Module\Everpsblog\Model;

class BlogImageModel extends \ObjectModel
{
    public $id_ever_image;
    public $id_element;
    public $image_type;
    public $image_link;
    public $id_shop;

    public static $definition = [
        'table' => 'ever_blog_image',
        'primary' => 'id_ever_image',
        'multilang' => false,
        'multishop' => true,
        'fields' => [
            'id_element' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'image_type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'image_link' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
        ],
    ];
}
