<?php

namespace App\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "locations",
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(name = "uniq_locations_country_id_region_id_city_id",
 *             columns={"country_id", "region_id", "city_id"}
 *         )
 *     }
 * )
 */
class Location extends IdentifiedAbstract
{

    use Timestamps;

    /**
     * @ORM\ManyToOne(targetEntity = "City")
     * @ORM\JoinColumn(
     *     name = "city_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var City
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity = "Country")
     * @ORM\JoinColumn(
     *     name = "country_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var Country
     */
    protected $country;

    /**
     * @ORM\ManyToOne(targetEntity = "Region")
     * @ORM\JoinColumn(
     *     name = "region_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var Region
     */
    protected $region;


    public function __construct(Country $country, Region $region, City $city)
    {
        $this->city = $city;
        $this->country = $country;
        $this->region = $region;
    }


    public function getCity(): City
    {
        return $this->city;
    }


    public function getCountry(): Country
    {
        return $this->country;
    }


    public function getRegion(): Region
    {
        return $this->region;
    }


    public function setCity(City $city): Location
    {
        $this->city = $city;

        return $this;
    }


    public function setCountry(Country $country): Location
    {
        $this->country = $country;

        return $this;
    }


    public function setRegion(Region $region): Location
    {
        $this->region = $region;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'country' => $this->getCountry(),
            'region' => $this->getRegion(),
            'city' => $this->getCity(),
        ];
    }

}
