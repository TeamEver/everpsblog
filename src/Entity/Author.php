<?php

namespace PrestaShop\Module\Everpsblog\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PrestaShop\\Module\\Everpsblog\\Repository\\AuthorRepository")
 * @ORM\Table(name="ever_blog_author")
 */
class Author
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(name="id_ever_author", type="integer") */
    private $id;

    /** @ORM\Column(name="id_employee", type="integer") */
    private $employeeId;

    /** @ORM\Column(name="nickhandle", type="string", length=255) */
    private $nickhandle;

    /** @ORM\Column(name="active", type="boolean", nullable=true) */
    private $active;
}
