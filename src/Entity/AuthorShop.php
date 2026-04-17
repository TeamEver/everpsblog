<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_author_shop") */
class AuthorShop
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Author", inversedBy="shops") @ORM\JoinColumn(name="id_ever_author", referencedColumnName="id_ever_author", nullable=false, onDelete="CASCADE") */
    private $author;

    /** @ORM\Id @ORM\Column(name="id_shop", type="integer") */
    private $shopId;
}
