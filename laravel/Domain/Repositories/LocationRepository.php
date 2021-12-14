<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\City;
use App\Domain\Entities\Country;
use App\Domain\Entities\Location;
use App\Domain\Entities\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;


class LocationRepository extends Repository
{

    /**
     * @var RegionRepository
     */
    protected $regionRepository;

    public function __construct(EntityManager $em, RegionRepository $regionRepository)
    {
        $this->regionRepository = $regionRepository;
        $this->em = $em;
        $this->er = $em->getRepository(Location::class);
    }


    /**
     * @param Country $country
     * @param Region $region
     * @param City $city
     * @return Location
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(Country $country, Region $region, City $city)
    {
        $entity = new Location($country, $region, $city);

        $this->em->persist($entity);

        return $entity;
    }


    /**
     * @return array|Location[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @param Region $region
     * @return ArrayCollection|City[]
     */
    public function findAvailableCities(Region $region)
    {
        $available = new ArrayCollection();
        $cities = $this->regionRepository->getCities($region);
        $usedCities = $this->findCities($region);

        foreach ($cities as $city) {
            if (!$usedCities->contains($city)) {
                $available->add($city);
            }
        }

        return $available;
    }


    /**
     * @param int $id
     * @return object|Location|null
     */
    public function findById(int $id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    /**
     * @param Region $region
     * @return ArrayCollection|City[]
     */
    public function findCities(Region $region)
    {
        $cities = new ArrayCollection();
        $locations = $this->er->findBy(['region' => $region]);

        /** @var Location $location */
        foreach ($locations as $location) {
            $cities->add($location->getCity());
        }

        return $cities;
    }


    /**
     * @param Location $entity
     * @param array $data
     * @return LocationRepository
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(Location $entity, array $data): LocationRepository
    {
        $validKeys = ['city', 'country', 'region'];

        $entity->fromArray($data, $validKeys);

        $this->em->persist($entity);

        return $this;
    }

}
