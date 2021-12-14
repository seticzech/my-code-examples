<?php

namespace App\Entity;

use App\Entity\Erp\User;
use App\Exception\AppException;
use App\Service\Erp\Acl\PermissionService;
use Psr\Container\ContainerInterface;
use Swagger\Annotations as SWG;

abstract class EntityAbstract
{
    const ACL_RESOURCE_NAME = null;
    
    use \App\Traits\AclCheckPermissionTrait;

    /**
     * @SWG\Property(
     *     type="object",
     * )
     * @var ContainerInterface
     */
    protected $container;


    public function getAuthenticatedUser(): ?User
    {
        
        if (!$this->getContainer()->has('security.token_storage')) {
            return null;
        }

        if (!$token = $this->getContainer()->get('security.token_storage')->getToken()) {
            return null;
        }
        
        if ($this->isAnonymousAuthentication($token)) {
            return null;
        }

        return $token->getUser();
    }
    
    protected function isAnonymousAuthentication(\Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token): bool
    {
        return !\is_object($token->getUser());
    }
    
    protected function isAllowed(string $action): bool
    {
        return $this->isUserActionAllowed($this->getAuthenticatedUser(), $action, $this);
    }
            
    protected function getPermissionService(): PermissionService
    {
        if (!$this->permissionService) {
            $this->permissionService = $this->getContainer()->get(PermissionService::class);
        }

        return $this->permissionService;
    }

    public function getContainer(): ContainerInterface
    {
        if (empty($this->container)) {
            throw new AppException("Missing service container in ".get_class($this).". Forgot to add ContainerAwareInterface?");
        }
        
        return $this->container;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

}
