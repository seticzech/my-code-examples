<?php

namespace App\Service\Erp\Sso;

use App\Entity\Erp\OAuth2\Client;
use App\Entity\Erp\User;
use App\Exception\BadRequestException;
use App\Exception\ForbiddenException;
use App\Repository\Erp\UserRepository;
use App\Service\Erp\Acl\RoleService;
use App\Service\Erp\Sso\Contract\SsoServiceInterface;
use Ramsey\Uuid\Uuid;

class SsoService implements SsoServiceInterface
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $tenantId;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var RoleService
     */
    protected $roleService;


    public function __construct(UserRepository $userRepository, Client $client, string $tenantId, $settings, RoleService $roleService)
    {
        $this->client = $client;
        $this->settings = $settings;
        $this->tenantId = $tenantId;
        $this->userRepository = $userRepository;
        $this->roleService = $roleService;
    }

    /**
     * @param $payload
     *
     * @return User
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function processPayload($payload): User
    {
        // So far payload is JWT
        $tokenParts = explode(".", $payload);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        $this->payload = $jwtPayload;

        if (!$this->verifyPayload()) {
            throw new BadRequestException("SSO payload verification failed.");
        }
        if (!$this->verifyUser()) {
            throw new ForbiddenException("SSO user verification failed.");
        }

        $user = $this->findUser();
        if (!$user) {
            $user = $this->registerUser();
        }

        $this->updateUser($user);

        if (!$user->getPassword()) {
            $this->userRepository->setUserPassword($user, (Uuid::uuid4())->toString());
        }

        $this->userRepository->save($user);

        return $user;
    }

    protected function findUser(): ?User
    {
        return $this->userRepository->findOneByEmail($this->payload->email);
    }

    protected function registerUser(): User
    {
        $user = new User();

        $user
            ->setEmail($this->payload->email)
            ->setTenantId($this->tenantId);
        
        $defaultRoles = $this->roleService->findDefaultRoles();
        
        foreach ($defaultRoles as $defaultRole) {
            $user->addUserRole($defaultRole);
        }

        return $user;
    }

    protected function updateUser(User $user)
    {
        //
    }

    protected function verifyPayload(): bool
    {
        return true;
    }

    protected function verifyUser(): bool
    {
        return true;
    }

}
