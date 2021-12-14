<?php

namespace App\Service\Erp\Sso;

use App\Entity\Erp\OAuth2\Client;
use App\Repository\Erp\UserRepository;
use App\Service\Erp\Sso\Contract\SsoServiceInterface;
use App\Service\Erp\Sso\Customer\Forfin;
use App\Service\Erp\Acl\RoleService;

class SsoServiceFactory
{

    private $userRepository;
    
    /**
     * @var RoleService
     */
    protected $roleService;


    public function __construct(UserRepository $userRepository, RoleService $roleService)
    {
        $this->userRepository = $userRepository;
        $this->roleService = $roleService;
    }

    public function createService(Client $client, string $tenantId, $settings): SsoServiceInterface
    {
        $service = new Forfin($this->userRepository, $client, $tenantId, $settings, $this->roleService);

        return $service;
    }

}
