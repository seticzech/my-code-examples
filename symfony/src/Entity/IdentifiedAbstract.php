<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class IdentifiedAbstract extends EntityAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type = "uuid", unique = true)
     * @ORM\GeneratedValue(strategy = "CUSTOM")
     * @ORM\CustomIdGenerator(class = "App\Doctrine\Generators\UuidGenerator")
     *
     * @Groups({"default", "oauth2", "anon"})
     * @SWG\Property(type="UUID")
     *
     * @var UuidInterface
     */
    protected $id;


    final public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    final public function setId(?UuidInterface $value): self
    {
        $this->id = $value;

        return $this;
    }

}
