<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_author_lang") */
class AuthorLang
{
    /** @ORM\Id @ORM\Column(name="id_ever_author", type="integer") */
    private $authorId;

    /** @ORM\Id @ORM\Column(name="id_lang", type="integer") */
    private $langId;

    /** @ORM\Column(name="meta_title", type="string", length=255, nullable=true) */
    private $metaTitle;

    /** @ORM\Column(name="link_rewrite", type="string", length=255, nullable=true) */
    private $linkRewrite;

    /** @ORM\Column(name="content", type="text") */
    private $content;
}
