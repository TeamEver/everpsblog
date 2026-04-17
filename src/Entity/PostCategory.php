<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="ever_blog_post_category") */
class PostCategory
{
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Post", inversedBy="postCategories") @ORM\JoinColumn(name="id_ever_post", referencedColumnName="id_ever_post", nullable=false, onDelete="CASCADE") */
    private $post;

    /** @ORM\Id @ORM\ManyToOne(targetEntity="Category", inversedBy="postCategories") @ORM\JoinColumn(name="id_ever_post_category", referencedColumnName="id_ever_category", nullable=false, onDelete="CASCADE") */
    private $category;
}
