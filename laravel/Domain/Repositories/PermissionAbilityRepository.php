<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\Permission\Ability;
use Doctrine\ORM\EntityManager;


class PermissionAbilityRepository extends Repository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Ability::class);
    }

}
