<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_category_lang") */
class CategoryLang
{
    /** @ORM\Id @ORM\Column(name="id_ever_category", type="integer") */
    private $categoryId;

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
