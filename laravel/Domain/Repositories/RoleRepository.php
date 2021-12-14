<?php

namespace App\Domain\Repositories;

use App\Base\Domain\TranslatableRepository;
use App\Domain\Entities\IdentifiedTranslatableAbstract;
use App\Domain\Entities\Permission;
use App\Domain\Entities\PermissionToRole;
use App\Domain\Entities\Role;
use App\Domain\Entities\RoleTranslation;
use App\Exceptions\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;


class RoleRepository extends TranslatableRepository
{

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->er = $em->getRepository(Role::class);
    }


    /**
     * @param string|null $internalName
     * @return Role
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(string $internalName = null): Role
    {
        $role = new Role($internalName);

        $this->em->persist($role);

        return $role;
    }


    /**
     * @return array|Role[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @param int $id
     * @return object|Role|null
     */
    public function findById($id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    /**
     * @param array $ids
     * @return Role[]|object[]
     */
    public function findByIds(array $ids)
    {
        return $this->er->findBy(['id' => $ids]);
    }


    /**
     * @param Role $role
     * @return ArrayCollection|PermissionToRole[]
     */
    public function getPermissions(Role $role)
    {
        return $role->getRolePermissions();
    }


    public function getPermissionsList(Role $role)
    {
        $result = [];

        foreach ($role->getRolePermissions() as $rolePermission) {
            $module = $rolePermission->getPermission()->getModule()
                ? $rolePermission->getPermission()->getModule()->getCode() . '.'
                : '';
            $class = $rolePermission->getPermission()->getClass();
            $ability = $rolePermission->getPermission()->getAbility()->getCode();
            $access = $rolePermission->getAccessType()->getCode();

            $key = $module . $class;

            if (!array_key_exists($key, $result)) {
                $result[$key] = [];
            }

            $result[$key][$ability] = $access;
        }

        return $result;
    }


    /**
     * @param IdentifiedTranslatableAbstract $entity
     * @param array $translations
     * @return RoleRepository
     * @throws \App\Exceptions\InsufficientDataException
     * @throws \Doctrine\ORM\ORMException
     */
    public function setTranslations(IdentifiedTranslatableAbstract $entity, array $translations): RoleRepository
    {
        $this->setTranslationsInternal($entity, RoleTranslation::class, $translations);

        return $this;
    }


    /**
     * @param Role $entity
     * @param array $data
     * @return RoleRepository
     * @throws \App\Exceptions\InsufficientDataException
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(Role $entity, array $data): RoleRepository
    {
        $validKeys = ['internalName'];

        $entity->fromArray($data, $validKeys);

        if (isset($data['translations'])) {
            $this->setTranslations($entity, $data['translations']);
        }

        if (isset($data['permissions'])) {
            $this->updatePermissions($entity, $data['permissions']);
        }

        $this->em->persist($entity);

        return $this;
    }


    /**
     * @param Role $role
     * @param array $data
     * @return RoleRepository
     * @throws InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updatePermissions(Role $role, array $data): RoleRepository
    {
        foreach ($role->getRolePermissions() as $rolePermission) {
            $role->removePermission($rolePermission);
            $this->em->remove($rolePermission);
            $this->em->flush($rolePermission);
        }

        $permissions = new ArrayCollection(
            $this->em->getRepository(Permission::class)->findAll()
        );
        $accessTypes = new ArrayCollection(
            $this->em->getRepository(Permission\AccessType::class)->findAll()
        );

        $criteria = Criteria::create();

        foreach ($data as $permId => $accTypeId) {
            $permission = $permissions->matching(
                $criteria->where(Criteria::expr()->eq('id', (int) $permId))
            )->first();
            if (!$permission) {
                throw new InvalidArgumentException("Permission ID: '{$permId}' not found.");
            }

            $accessType = $accessTypes->matching(
                $criteria->where(Criteria::expr()->eq('id', (int) $accTypeId))
            )->first();
            if (!$accessType) {
                throw new InvalidArgumentException("Permission access type ID: '{$accTypeId}' not found.");
            }

            $newPermission = new PermissionToRole($role, $permission, $accessType);

            $role->addPermission($newPermission);
        }

        $this->em->persist($role);

        return $this;
    }

}
