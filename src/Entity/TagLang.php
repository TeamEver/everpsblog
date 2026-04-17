<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_tag_lang") */
class TagLang
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Tag", inversedBy="translations") @ORM\JoinColumn(name="id_ever_tag", referencedColumnName="id_ever_tag", nullable=false, onDelete="CASCADE") */
    private $tag;

    /** @ORM\Id @ORM\Column(name="id_lang", type="integer") */
    private $langId;

    /** @ORM\Column(name="title", type="string", length=255) */
    private $title;

    /** @ORM\Column(name="meta_title", type="string", length=255, nullable=true) */
    private $metaTitle;

    /** @ORM\Column(name="meta_description", type="string", length=255, nullable=true) */
    private $metaDescription;

    /** @ORM\Column(name="link_rewrite", type="string", length=255, nullable=true) */
    private $linkRewrite;

    /** @ORM\Column(name="content", type="text") */
    private $content;

    /** @ORM\Column(name="bottom_content", type="text", nullable=true) */
    private $bottomContent;
}
