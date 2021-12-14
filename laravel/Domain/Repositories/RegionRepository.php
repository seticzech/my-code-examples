<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\City;
use App\Domain\Entities\Country;
use App\Domain\Entities\Region;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;


class RegionRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Region::class);
    }


    /**
     * @param Country $country
     * @param string $name
     * @return Region
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(Country $country, string $name): Region
    {
        $entity = new Region($country, $name);

        $this->em->persist($entity);

        return $entity;
    }


    /**
     * @param Region $region
     * @return ArrayCollection|City[]
     */
    public function getCities(Region $region)
    {
        return $region->getCities();
    }


    /**
     * @return array|Region[]
     */
    public function findAll()
    {
        return parent::findAll();
    }

    /**
     * @param int $id
     * @return object|Region|null
     */
    public function findById(int $id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    public function hasCity(Region $region, City $city)
    {
        return $region->hasCity($city);
    }


    /**
     * @param Region $entity
     * @param array $data
     * @return RegionRepository
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(Region $entity, array $data): RegionRepository
    {
        $validKeys = ['country', 'name'];

        $entity->fromArray($data, $validKeys);

        $this->em->persist($entity);

        return $this;
    }


}
