<?php

namespace App\Entity\Erp\Acl;

use App\Contract\Entity\TenantAwareInterface;
use App\Entity\Erp\Module;
use App\Entity\IdentifiedAbstract;
use App\Traits\Entity\SoftDeletableTrait;
use App\Traits\Entity\TenantMaybeTrait;
use App\Traits\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.acl_roles")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Role extends IdentifiedAbstract implements TenantAwareInterface
{

    const CODE_ADMIN = 'ROLE_ADMIN';
    const CODE_USER = 'ROLE_USER';
            
    use SoftDeletableTrait,
        TenantMaybeTrait,
        TimestampableTrait;
    
    /**
     * @ORM\ManyToOne(targetEntity = "App\Entity\Erp\Module")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id", nullable=true)
     * 
     * @Groups({"default"})
     *
     * @var Module
     */
    protected $module;

    /**
     * @ORM\Column(type = "string", length = 128)
     *
     * @Groups({"default"})
     *
     * @var string|null
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", length = 64)
     *
     * @Groups({"default"})
     * 
     * @var string|null
     */
    protected $code;

    /**
     * @ORM\Column(type = "boolean", name = "is_default", options = {"default" : false})
     * 
     * @Groups({"default"})
     *
     * @var bool
     */
    protected $isDefault = false;

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $value): self
    {
        $this->module = $value;

        return $this;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $value): self
    {
        $this->name = $value;

        return $this;
    }
    
    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        
        return $this;
    }
    
    public function isAdmin(): bool
    {
        return $this->code === self::CODE_ADMIN;
    }
    
    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }
}
