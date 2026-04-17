<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_tag") */
class PostTag
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Post", inversedBy="postTags") @ORM\JoinColumn(name="id_ever_post", referencedColumnName="id_ever_post", nullable=false, onDelete="CASCADE") */
    private $post;

    /** @ORM\Id @ORM\ManyToOne(targetEntity="Tag", inversedBy="postTags") @ORM\JoinColumn(name="id_ever_post_tag", referencedColumnName="id_ever_tag", nullable=false, onDelete="CASCADE") */
    private $tag;
}
