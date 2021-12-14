<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\Navigation;
use Doctrine\ORM\EntityManager;


class NavigationRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Navigation::class);
    }


    /**
     * Create root navigation
     *
     * @param string $code
     * @param int $sortOrder
     * @return Navigation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $code): Navigation
    {
        $navigation = new Navigation($code);

        $this->em->persist($navigation);

        return $navigation;
    }


    /**
     * @return array|Navigation[]
     */
    public function findAllParent()
    {
        return $this->er->findBy(['parent' => null], ['sortOrder' => 'ASC']);
    }

}
