<?php

namespace App\Domain\Repositories;

use App\Base\Domain\Repository;
use App\Domain\Entities\Role;
use App\Domain\Entities\User;
use App\Exceptions\InsufficientDataException;
use App\Security\Passwords\Hasher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;


class UserRepository extends Repository
{

    /**
     * @var RoleRepository
     */
    protected $roleRepository;


    public function __construct(EntityManager $em, RoleRepository $roleRepository)
    {
        $this->em = $em;
        $this->er = $em->getRepository(User::class);
        $this->roleRepository = $roleRepository;
    }


    /**
     * @param string $email
     * @param string|null $username
     * @return User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $email, string $username = null): User
    {
        $user = new User($email);

        $user->setUsername($username);

        $this->em->persist($user);

        return $user;
    }


    /**
     * @param array $data
     * @return User
     * @throws InsufficientDataException
     * @throws \App\Exceptions\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createFromData(array $data)
    {
        if (!isset($data['email'])) {
            throw new InsufficientDataException('When creating new user from data, \'email\' key is mandatory.');
        }
        if (!isset($data['password'])) {
            throw new InsufficientDataException('When creating new user from data, \'password\' key is mandatory.');
        }

        $user = new User($data['email']);
        $this->setPassword($user, $data['password']);

        $this->em->persist($user);

        $this->update($user, $data);

        return $user;
    }


    public function getPermissions(User $user): ArrayCollection
    {
        $result = [];

        foreach ($user->getRoles() as $role) {
            foreach ($this->roleRepository->getPermissions($role) as $permission) {
                $id = $permission->getPermission()->getId();
                $result[$id] = $permission;
            }
        }

        return new ArrayCollection(array_values($result));
    }


    public function getPermissionsList(User $user): array
    {
        $result = [];

        foreach ($user->getRoles() as $role) {
            $result = array_merge(
                $result,
                $this->roleRepository->getPermissionsList($role)
            );
        }

        return $result;
    }


    /**
     * @param User $user
     * @return Role[]
     */
    public function getRoles(User $user)
    {
        return $user->getRoles();
    }


    /**
     * @return array|User[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    public function findByEmail(string $email)
    {
        return $this->er->findOneBy(['email' => $email]);
    }


    /**
     * @param int $id
     * @return object|User|null
     */
    public function findById(int $id)
    {
        return $this->er->findOneBy(['id' => $id]);
    }


    /**
     * @param string $username
     * @return object|User|null
     */
    public function findByUsername(string $username)
    {
        return $this->er->findOneBy(['username' => $username]);
    }


    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     */
    protected function removeRoles(User $user)
    {
        foreach ($user->getRoles() as $role) {
            $user->removeRole($role);
        }

        $this->em->persist($user);
    }


    /**
     * @param User $user
     * @param array $roleIds
     * @throws \Doctrine\ORM\ORMException
     */
    protected function setRoles(User $user, array $roleIds)
    {
        $roles = $this->roleRepository->findByIds($roleIds);

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->em->persist($user);
    }


    /**
     * @param User $user
     * @param array $data
     * @return UserRepository
     * @throws \App\Exceptions\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(User $user, array $data): UserRepository
    {
        $validKeys = ['firstName', 'lastName', 'email', 'username'];

        $user->fromArray($data, $validKeys);

        if (isset($data['password'])) {
            $this->setPassword($user, $data['password']);
        }

        if (array_key_exists('roles', $data)) {
            $this->removeRoles($user);
            $this->setRoles($user, $data['roles']);
        }

        return $this;
    }


    /**
     * @param User $user
     * @param string $password
     * @return UserRepository
     * @throws \App\Exceptions\InvalidArgumentException
     */
    public function setPassword(User $user, string $password): UserRepository
    {
        $user->setPassword(
            (new Hasher())->hash($password)
        );

        return $this;
    }

}
