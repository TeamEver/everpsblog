<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_lang") */
class PostLang
{
    /** @ORM\Id @ORM\Column(name="id_ever_post", type="integer") */
    private $postId = 0;

    /** @ORM\Id @ORM\Column(name="id_lang", type="integer") */
    private $langId = 0;

    /** @ORM\Column(name="title", type="string", length=255) */
    private $title = '';

    /** @ORM\Column(name="meta_title", type="string", length=255, nullable=true) */
    private $metaTitle;

    /** @ORM\Column(name="meta_description", type="string", length=255, nullable=true) */
    private $metaDescription;

    /** @ORM\Column(name="link_rewrite", type="string", length=255, nullable=true) */
    private $linkRewrite;

    /** @ORM\Column(name="content", type="text") */
    private $content = '';

    /** @ORM\Column(name="excerpt", type="string", length=255, nullable=true) */
    private $excerpt;

    public static function create(
        ?int $postId,
        int $langId,
        string $title,
        string $content,
        string $excerpt,
        string $metaTitle,
        string $metaDescription,
        string $linkRewrite
    ): self {
        $self = new self();
        $self->postId = (int) $postId;
        $self->langId = $langId;
        $self->title = $title;
        $self->content = $content;
        $self->excerpt = $excerpt;
        $self->metaTitle = $metaTitle;
        $self->metaDescription = $metaDescription;
        $self->linkRewrite = $linkRewrite;

        return $self;
    }

    public function getPostId(): int
    {
        return (int) $this->postId;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
    }

    public function getLangId(): int
    {
        return (int) $this->langId;
    }

    public function getTitle(): string
    {
        return (string) $this->title;
    }

    public function getContent(): string
    {
        return (string) $this->content;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function getLinkRewrite(): ?string
    {
        return $this->linkRewrite;
    }
}
