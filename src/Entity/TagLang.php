<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_tag_lang") */
class TagLang
{
    /** @ORM\Id @ORM\Column(name="id_ever_tag", type="integer") */
    private $tagId;

    /** @ORM\Id @ORM\Column(name="id_lang", type="integer") */
    private $langId;

    /** @ORM\Column(name="title", type="string", length=255) */
    private $title;

    /** @ORM\Column(name="link_rewrite", type="string", length=255, nullable=true) */
    private $linkRewrite;
}
