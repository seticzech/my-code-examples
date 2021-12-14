<?php

namespace App\Entity\Erp\Acl;

use App\Entity\Erp\User;

class PermissionRequest
{
    
    /**
     * @var User
     */
    protected $user;
    
    /**
     * @var \App\Entity\IdentifiedAbstract
     */
    protected $entity;
    
    /**
     * @var string
     */
    protected $resource;
    
    /**
     * @var string
     */
    protected $action;
    
    
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    public function getEntity(): ?\App\Entity\IdentifiedAbstract
    {
        return $this->entity;
    }

    public function setEntity(\App\Entity\IdentifiedAbstract $entity): self
    {
        $this->entity = $entity;
        
        return $this;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;
        
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        
        return $this;
    }

}
