<?php

namespace App\Domain\Entities;

use App\Base\Domain\Entity;
use App\Domain\Entities\Permission\AccessType;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "permissions_to_roles")
 */
class PermissionToRole extends Entity
{

    use Timestamps;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity = "App\Domain\Entities\Permission\AccessType")
     * @var AccessType
     */
    protected $accessType;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity = "Permission")
     * @var Permission
     */
    protected $permission;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity = "Role")
     * @var Role
     */
    protected $role;


    public function __construct(Role $role, Permission $permission, AccessType $accessType)
    {
        $this->accessType = $accessType;
        $this->permission = $permission;
        $this->role = $role;
    }


    public function getAccessType(): AccessType
    {
        return $this->accessType;
    }


    public function getPermission(): Permission
    {
        return $this->permission;
    }


    public function getRole(): Role
    {
        return $this->role;
    }


    public function toArray(): array
    {
        return [
//            'module' => $this->getPermission()->getModule() ? $this->getPermission()->getModule()->getCode() : null,
//            'field' => $this->getPermission()->getField(),
//            'action' => $this->getPermission()->getAction()->getCode(),
//            'access' => $this->getAccessType()->getCode(),
            'accessType' => $this->getAccessType(),
            'permission' => $this->getPermission(),
            'role' => $this->getRole(),
        ];
    }

}
