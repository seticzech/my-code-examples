<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "modules")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Module extends IdentifiedAbstract
{

    use Timestamps;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", length = 20, nullable = false)
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type = "datetime", name = "deleted_at", nullable = true)
     * @var DateTime|null
     */
    protected $deletedAt;


    public function getName(): string
    {
        return $this->name;
    }


    public function getCode(): string
    {
        return $this->code;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'code' => $this->getCode(),
        ];
    }

}
