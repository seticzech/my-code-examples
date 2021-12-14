<?php

namespace App\Domain\Entities;

use App\Traits\Domain\Entities\SoftDeleteable;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;
use PHPUnit\Framework\Constraint\Count;


/**
 * @ORM\Entity
 * @ORM\Table(name = "countries")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Country extends IdentifiedAbstract
{

    use SoftDeleteable, Timestamps;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", name = "iso_code_2", length = 2, nullable = false)
     * @var string
     */
    protected $isoCode2;

    /**
     * @ORM\Column(type = "string", name = "iso_code_3", length = 3, nullable = false)
     * @var string
     */
    protected $isoCode3;

    /**
     * @ORM\Column(type = "boolean", name = "is_active", options = {"default" : true})
     * @var bool
     */
    protected $isActive;

    /**
     * @ORM\OneToMany(targetEntity = "Region", mappedBy = "country")
     * @var Region[]
     */
    protected $regions;


    public function __construct(string $name, string $isoCode2, string $isoCode3)
    {
        $this->name = $name;
        $this->isoCode2 = strtoupper($isoCode2);
        $this->isoCode3 = strtoupper($isoCode3);
        $this->isActive = true;
        $this->regions = new ArrayCollection();
    }


    public function addRegion(Region $region): Country
    {
        if (!$this->regions->contains($region)) {
            $this->regions->add($region);
        }

        return $this;
    }


    public function getIsoCode2(): string
    {
        return $this->isoCode2;
    }


    public function getIsoCode3(): string
    {
        return $this->isoCode3;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getRegions()
    {
        return $this->regions;
    }


    public function hasRegion(Region $region): bool
    {
        return $this->regions->contains($region);
    }


    public function setName(string $value): Country
    {
        $this->name = $value;

        return $this;
    }


    public function setIsoCode2(string $value): Country
    {
        $this->isoCode2 = $value;

        return $this;
    }


    public function setIsoCode3(string $value): Country
    {
        $this->isoCode3 = $value;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'isoCode2' => $this->getIsoCode2(),
            'isoCode3' => $this->getIsoCode3(),
            'regionsCount' => $this->getRegions()->count(),
            'deletedAt' => $this->getDeletedAt(),
        ];
    }

}
