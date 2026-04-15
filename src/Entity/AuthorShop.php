<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_author_shop") */
class AuthorShop
{
    /** @ORM\Id @ORM\Column(name="id_ever_author", type="integer") */
    private $authorId;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
