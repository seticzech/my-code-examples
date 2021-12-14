<?php

namespace App\Service\Erp\Acl;

use App\Collection\Acl\Permission\PermissionCollection;
use App\Entity\Erp\Acl\PermissionRequest;
use App\Entity\Erp\Acl\PermissionStatus;
use App\Entity\Erp\Acl\Role;
use App\Entity\Erp\User;
use App\Entity\IdentifiedAbstract;
use App\Exception\Service\Permission\AdminRoleFoundException;
use App\Exception\Service\Permission\NoPermissionFoundException;
use App\Exception\Service\Permission\UserMissingRolesException;
use App\Repository\Erp\Acl\PermissionRepository;

class PermissionService
{
    
    /**
     * @var PermissionRepository
     */
    protected $permissionRepository;
    
    
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }
    
    public function checkPermission(PermissionRequest $permissionRequest): PermissionStatus 
    {
        try {
            $roleIds = $this->getRoleIds($permissionRequest);
            $permissions = $this->findPermissions($roleIds, $permissionRequest->getResource(), $permissionRequest->getAction());
            
            return $this->createPermissionStatus($permissions, $permissionRequest->getUser(), $permissionRequest->getEntity());
        } catch (AdminRoleFoundException $exc) {
            return new PermissionStatus(true);
        } catch (NoPermissionFoundException $exc) {
            return new PermissionStatus(false);
        }
    }
    
    protected function getRoleIds(PermissionRequest $permissionRequest): array
    {
        $roleIds = [];
        foreach ($permissionRequest->getUser()->getUserRoles() as $userRole) {
            /** @var Role $userRole */
            if ($userRole->isAdmin()) {
                throw new AdminRoleFoundException;
            }
            
            $roleIds[] = $userRole->getId();
        }
        
        if (!$roleIds) {
            throw new UserMissingRolesException("User {$permissionRequest->getUser()->getId()} is missing roles.");
        }
        
        return $roleIds;
    }
    
    protected function findPermissions(array $roleIds, string $resource, string $action): PermissionCollection
    {
        $permissions = $this->permissionRepository->findForAcl($roleIds, $resource, $action);
        
        if (!$permissions) {
            throw new NoPermissionFoundException;
        }
        
        return new PermissionCollection($permissions);
    }
    
    protected function createPermissionStatus(PermissionCollection $permissions, User $currentUser, ?IdentifiedAbstract $entity): PermissionStatus
    {
        $permission = $permissions->getBestPermission();
        
        if ($permission->isOptionAccessOnlyMine()) {
            $isGranted = $entity ? $entity->getUser()->getId() === $currentUser->getId() : true;
        } else {
            $isGranted = true;
        }
        
        return new PermissionStatus($isGranted, $permission->getOptions());
    }

}
