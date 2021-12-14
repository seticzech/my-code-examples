<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\Permission\Group;
use Doctrine\ORM\EntityManager;


class PermissionGroupRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Group::class);
    }


    /**
     * @return array|Group[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @return array|Group[]
     */
    public function findAllParents()
    {
        return $this->er->findBy(['parent' => null], ['sortOrder' => 'ASC']);
    }


}
