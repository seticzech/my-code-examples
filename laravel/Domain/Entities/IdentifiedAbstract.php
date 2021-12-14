<?php

namespace App\Domain\Entities;

use App\Base\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;


abstract class IdentifiedAbstract extends Entity
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "IDENTITY")
     * @ORM\Column(type = "integer")
     */
    protected $id;


    final public function getId(): int
    {
        return (int) $this->id;
    }

}
