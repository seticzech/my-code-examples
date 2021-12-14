<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\City;
use App\Domain\Entities\Region;
use Doctrine\ORM\EntityManager;


class CityRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(City::class);
    }


    /**
     * @param Region $region
     * @param string $name
     * @return City
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(Region $region, string $name): City
    {
        $entity = new City($region, $name);

        $this->em->persist($entity);

        return $entity;
    }


    /**
     * @return array|City[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @param int $id
     * @return object|City|null
     */
    public function findById(int $id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    /**
     * @param City $entity
     * @param array $data
     * @return CityRepository
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(City $entity, array $data): CityRepository
    {
        $validKeys = ['name', 'region'];

        $entity->fromArray($data, $validKeys);

        $this->em->persist($entity);

        return $this;
    }

}
