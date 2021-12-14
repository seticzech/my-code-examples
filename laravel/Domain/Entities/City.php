<?php

namespace App\Domain\Entities;

use App\Traits\Domain\Entities\SoftDeleteable;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "cities")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class City extends IdentifiedAbstract
{

    use SoftDeleteable, Timestamps;

    /**
     * @ORM\ManyToOne(targetEntity = "Region", inversedBy = "cities")
     * @ORM\JoinColumn(
     *     name = "region_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var Region
     */
    protected $region;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $name;


    public function __construct(Region $region, string $name)
    {
        $this->name = $name;
        $this->region = $region;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getRegion(): Region
    {
        return $this->region;
    }


    public function setName(string $value): City
    {
        $this->name = $value;

        return $this;
    }


    public function setRegion(Region $region): City
    {
        $this->region = $region;
        $region->addCity($this);

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'region' => $this->getRegion(),
            'deletedAt' => $this->getDeletedAt(),
        ];
    }

}
