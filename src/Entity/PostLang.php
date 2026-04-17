<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ever_blog_post_lang")
 */
class PostLang
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId;

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

    /** @ORM\Column(name="excerpt", type="string", length=255, nullable=true) */
    private $excerpt;

    public static function create(
        ?int $postId,
        int $langId,
        string $title,
        string $content,
        ?string $excerpt,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $linkRewrite
    ): self {
        $postLang = new self();
        $postLang->postId = $postId;
        $postLang->langId = $langId;
        $postLang->title = $title;
        $postLang->content = $content;
        $postLang->excerpt = $excerpt;
        $postLang->metaTitle = $metaTitle;
        $postLang->metaDescription = $metaDescription;
        $postLang->linkRewrite = $linkRewrite;

        return $postLang;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }
}
