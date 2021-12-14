<?php

namespace App\Entity\Erp;

use App\Entity\IdentifiedAbstract;
use App\Traits\Entity\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Unique constraint "uniq_core_navigations_module_id_parent_id_code" has only one column
 * because Doctrine does not support COALESCE on columns and that columns are invisible for Doctrine.
 *
 * In fact constraint has this format:
 *
 * CREATE UNIQUE INDEX uniq_core_navigations_module_id_parent_id_code ON bb_erp.core_navigations
 * (COALESCE(module_id, '00000000-0000-0000-0000-000000000000'::uuid), COALESCE(parent_id, '00000000-0000-0000-0000-000000000000'::uuid), code)
 *
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.core_navigations",
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(name = "uniq_core_navigations_module_id_parent_id_code",
 *             columns = {"code"}
 *         )
 *     }
 * )
 */
class Navigation extends IdentifiedAbstract
{

    use TimestampableTrait;

    /**
     * @ORM\OneToMany(targetEntity = "Navigation", mappedBy = "parent")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     * @Groups({"default"})
     *
     * @var ArrayCollection|Navigation[]
     */
    protected $children;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = false)
     * @Groups({"default"})
     * 
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = false)
     * @Groups({"default"})
     * 
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = true)
     * @Groups({"default"})
     * 
     * @var string
     */
    protected $icon;

    /**
     * @ORM\Column(type = "boolean", name = "is_active", options = {"default" : true})
     *
     * @var bool
     */
    protected $isActive;

    /**
     * @ORM\ManyToOne(targetEntity = "Module")
     * @ORM\JoinColumn(nullable = false)
     * 
     * @var Module
     */
    protected $module;

    /**
     * @ORM\ManyToOne(targetEntity = "Navigation", inversedBy = "children")
     *
     * @var Navigation|null
     */
    protected $parent;

    /**
     * @ORM\Column(type = "smallint", name = "sort_order", nullable = false, options = {"default" : 1})
     *
     * @var int
     */
    protected $sortOrder;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = true)
     * @Groups({"default"})
     * 
     * @var string
     */
    protected $url;

//    /**
//     * @ORM\OneToOne(targetEntity = "Permission")
//     *
//     * @var Permission
//     */
//    protected $permission;


    public function __construct(string $code)
    {
        $this->code = $code;
        $this->children = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|Navigation[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getCode(): string
    {
        return $this->code;
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function getParent(): ?Navigation
    {
        return $this->parent;
    }

//    public function getPermission(): ?Permission
//    {
//        return $this->permission;
//    }
//
//    public function getPermissionCode(): ?string
//    {
//        return $this->getPermission()
//            ? $this->getPermission()->getCode()
//            : null;
//    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

}
