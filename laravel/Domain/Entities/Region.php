<?php

namespace App\Domain\Entities;

use App\Base\Domain\Entity;
use App\Traits\Domain\Entities\SoftDeleteable;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "regions")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Region extends IdentifiedAbstract
{

    use SoftDeleteable, Timestamps;

    /**
     * @ORM\OneToMany(targetEntity = "City", mappedBy = "region")
     * @var City[]
     */
    protected $cities;

    /**
     * @ORM\ManyToOne(targetEntity = "Country", inversedBy = "regions")
     * @ORM\JoinColumn(
     *     name = "country_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var Country
     */
    protected $country;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $name;


    public function __construct(Country $country, string $name)
    {
        $this->country = $country;
        $this->name = $name;
        $this->cities = new ArrayCollection();
    }


    public function addCity(City $city): Region
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
        }

        return $this;
    }


    /**
     * @return ArrayCollection|City[]
     */
    public function getCities()
    {
        return $this->cities;
    }


    public function getCountry(): Country
    {
        return $this->country;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function hasCity(City $city): bool
    {
        return $this->cities->contains($city);
    }


    public function setCountry(Country $country): Region
    {
        $this->country = $country;
        $country->addRegion($this);

        return $this;
    }


    public function setName(string $value): Region
    {
        $this->name = $value;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'country' => $this->getCountry(),
            'deletedAt' => $this->getDeletedAt(),
        ];
    }

}
