<?php

namespace App\Entity\Erp\Acl;

use App\Entity\Erp\Module;
use App\Entity\IdentifiedAbstract;
use App\Traits\Entity\SoftDeletableTrait;
use App\Traits\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.acl_permissions")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Permission extends IdentifiedAbstract
{

    use SoftDeletableTrait,
        TimestampableTrait;
    
    const ACL_OPTION_ACCESS = 'access';
    
    const ACL_ACCESS_LEVEL_MINE = 'MINE';
    
    /**
     * @ORM\ManyToOne(targetEntity = "App\Entity\Erp\Module")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id", nullable=true)
     * 
     * @var Module
     */
    protected $module;
    
    /**
     * @ORM\ManyToOne(targetEntity = "App\Entity\Erp\Acl\Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * 
     * @var Role
     */
    protected $role;

    /**
     * @ORM\Column(type = "string", length = 256)
     *
     * @var string
     */
    protected $resource;

    /**
     * @ORM\Column(type = "string", length = 64)
     *
     * @var string
     */
    protected $action;
            
    /**
     * @ORM\Column(type = "json_array", options = {"jsonb": true, "default": "{}"})
     *
     * @var array
     */
    protected $options = [];

    
    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $value): self
    {
        $this->module = $value;

        return $this;
    }
    
    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;
        
        return $this;
    }
    
    public function getResource(): ?string
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
    
    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        
        return $this;
    }
    
    public function hasAccessOption(): bool
    {
        return !empty($this->getOptions()[self::ACL_OPTION_ACCESS]);
    }
    
    public function getAccessOption(): ?string
    {
        return $this->getOptions()[self::ACL_OPTION_ACCESS] ?? null;
    }
    
    public function isOptionAccessOnlyMine(): bool
    {
        return $this->hasAccessOption() && $this->getAccessOption() === self::ACL_ACCESS_LEVEL_MINE;
    }

}
