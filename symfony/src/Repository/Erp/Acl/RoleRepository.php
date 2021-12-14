<?php

namespace App\Repository\Erp\Acl;

use App\Entity\Erp\Acl\Role;
use App\Repository\RepositoryAbstract;

class RoleRepository extends RepositoryAbstract
{

    /**
     * @var string
     */
    protected static $entityClass = Role::class;

    
    public function findUserRole(): Role
    {
        return $this->findOneBy([
            'code' => Role::CODE_USER
        ]);
    }
    
    public function findAdminRole(): Role
    {
        return $this->findOneBy([
            'code' => Role::CODE_ADMIN
        ]);
    }
}
