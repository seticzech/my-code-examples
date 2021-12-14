<?php

namespace App\Domain\Entities;

use App\Contracts\TranslatableEntityInterface;
use App\Domain\Entities\Permission\AccessType;
use App\Domain\Entities\Permission\Ability;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name = "permissions")
 */
class Permission extends IdentifiedTranslatableAbstract implements TranslatableEntityInterface
{

    /**
     * @ORM\ManyToOne(targetEntity = "App\Domain\Entities\Permission\Group")
     * @ORM\JoinColumn(
     *     name = "group_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var Permission\Group
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity = "Module")
     * @ORM\JoinColumn(
     *     name = "module_id",
     *     referencedColumnName = "id",
     *     nullable = true
     * )
     * @var Module
     */
    protected $module;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $class;

    /**
     * @ORM\ManyToOne(targetEntity = "App\Domain\Entities\Permission\Ability")
     * @ORM\JoinColumn(nullable = false)
     * @var Permission\Ability
     */
    protected $ability;

    /**
     * @ORM\OneToMany(targetEntity = "App\Domain\Entities\Permission\AccessTypeToPermission", mappedBy = "permission", cascade = {"persist", "remove"})
     * @var ArrayCollection
     */
    protected $permissionAccessTypes;

    /**
     * @ORM\OneToMany(targetEntity = "PermissionTranslation", mappedBy = "source", cascade = {"persist", "remove"}, fetch = "EAGER")
     * @var ArrayCollection
     */
    protected $translations;


    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }


    public function getAbility(): Ability
    {
        return $this->ability;
    }


    /**
     * @return ArrayCollection|AccessType[]
     */
    public function getAccessTypes()
    {
        $items = [];

        foreach ($this->permissionAccessTypes as $permissionAccessType) {
            $items[] = $permissionAccessType->getAccessType();
        }

        return new ArrayCollection($items);
    }


    public function getCode(): string
    {
        return $this->getModule()
            ? $this->getModule()->getCode() . '.' . $this->getClass()
            : $this->getClass();
    }


    public function getClass(): string
    {
        return $this->class;
    }


    public function getModule(): ?Module
    {
        return $this->module;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'module' => ($this->getModule()) ? $this->getModule()->toArray() : null,
            'class' => $this->getClass(),
            'ability' => $this->getAbility()->toArray(),
            'accessTypes' => $this->getAccessTypes()->toArray(),
            'translations' => $this->getTranslations()->toArray(),
        ];
    }

}
