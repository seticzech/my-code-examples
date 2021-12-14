<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\City;
use App\Domain\Entities\Country;
use App\Domain\Entities\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;


class CountryRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Country::class);
    }


    /**
     * @param string $name
     * @param string $isoCode2
     * @param string $isoCode3
     * @return Country
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(string $name, string $isoCode2, string $isoCode3): Country
    {
        $country = new Country($name, $isoCode2, $isoCode3);

        $this->em->persist($country);

        return $country;
    }


    /**
     * @return array|Country[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @param int $id
     * @return object|Country|null
     */
    public function findById(int $id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    /**
     * @param Country $country
     * @return ArrayCollection|Region[]
     */
    public function getRegions(Country $country)
    {
        return $country->getRegions();
    }


    public function hasRegion(Country $country, Region $region)
    {
        return $country->hasRegion($region);
    }


    /**
     * @param Country $entity
     * @param array $data
     * @return CountryRepository
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(Country $entity, array $data): CountryRepository
    {
        $validKeys = ['name', 'isoCode2', 'isoCode3'];

        $entity->fromArray($data, $validKeys);

        $this->em->persist($entity);

        return $this;
    }


}
