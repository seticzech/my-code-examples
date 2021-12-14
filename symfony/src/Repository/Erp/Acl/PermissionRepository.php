<?php

namespace App\Repository\Erp\Acl;

use App\Entity\Erp\Acl\Permission;
use App\Repository\RepositoryAbstract;

class PermissionRepository extends RepositoryAbstract
{

    /**
     * @var string
     */
    protected static $entityClass = Permission::class;

    
    public function findForAcl(array $roleIds, string $resource, string $action)
    {
        return $this->findBy([
            'role' => $roleIds,
            'resource' => $resource,
            'action' => $action
        ]);
    }
}
