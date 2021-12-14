<?php

namespace App\Domain\Entities;

use App\Contracts\TranslatableEntityInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "roles")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Role extends IdentifiedTranslatableAbstract implements TranslatableEntityInterface
{

    use Timestamps;

    /**
     * @ORM\Column(type = "string", name = "internal_name", length = 64, nullable = true)
     * @var string
     */
    protected $internalName;

    /**
     * @ORM\Column(type = "boolean", name = "is_super_admin", options = {"default" : false})
     * @var bool
     */
    protected $isSuperAdmin;

    /**
     * @ORM\Column(type = "datetime", name = "deleted_at", nullable = true)
     * @var DateTime|null
     */
    protected $deletedAt;

    /**
     * @ORM\OneToMany(targetEntity = "PermissionToRole", mappedBy = "role", cascade = {"persist", "remove"})
     * @var ArrayCollection
     */
    protected $rolePermissions;

    /**
     * @ORM\OneToMany(targetEntity = "RoleTranslation", mappedBy = "source", cascade = {"persist", "remove"}, fetch = "EAGER")
     * @var ArrayCollection
     */
    protected $translations;


    public function __construct(string $internalName = null)
    {
        $this->internalName = $internalName;
        $this->isSuperAdmin = false;
        $this->rolePermissions = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }


    public function addPermission(PermissionToRole $entity): Role
    {
        if (!$this->rolePermissions->contains($entity)) {
            $this->rolePermissions->add($entity);
        }

        return $this;
    }


    public function getInternalName(): ?string
    {
        return $this->internalName;
    }


    public function getIsSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }


    /**
     * @return ArrayCollection|PermissionToRole[]
     */
    public function getRolePermissions()
    {
        return $this->rolePermissions;
    }


    public function removePermission(PermissionToRole $entity): Role
    {
        if ($this->rolePermissions->contains($entity)) {
            $this->rolePermissions->removeElement($entity);
        }

        return $this;
    }


    public function setInternalName(?string $value): Role
    {
        $this->internalName = $value;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'internalName' => $this->getInternalName(),
            'isSuperAdmin' => $this->getIsSuperAdmin(),
            'translations' => $this->getTranslations()->toArray(),
        ];
    }

}
