<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "navigations",
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(name = "uniq_navigations_code",
 *             columns = {"code"},
 *             options = {"where": "((module_id IS NULL) AND (parent_id IS NULL))"}
 *         ),
 *         @ORM\UniqueConstraint(name = "uniq_navigations_parent_id_code",
 *             columns = {"parent_id", "code"},
 *             options = {"where": "((module_id IS NOT NULL) AND (parent_id IS NULL))"}
 *         ),
 *         @ORM\UniqueConstraint(name = "uniq_navigations_module_id_code",
 *             columns = {"module_id", "code"},
 *             options = {"where": "((module_id IS NULL) AND (parent_id IS NOT NULL))"}
 *         ),
 *         @ORM\UniqueConstraint(name = "uniq_navigations_module_id_parent_id_code",
 *             columns = {"module_id", "parent_id", "code"},
 *             options = {"where": "((module_id IS NOT NULL) AND (parent_id IS NOT NULL))"}
 *         )
 *     }
 * )
 */
class Navigation extends IdentifiedAbstract
{

    use Timestamps;

    /**
     * @ORM\OneToMany(targetEntity = "Navigation", mappedBy = "parent")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = false)
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = true)
     * @var string
     */
    protected $icon;

    /**
     * @ORM\Column(type = "boolean", name = "is_active", options = {"default" : true})
     * @var bool
     */
    protected $isActive;

    /**
     * @ORM\ManyToOne(targetEntity = "Module")
     * @ORM\JoinColumn(
     *     name = "module_id",
     *     referencedColumnName = "id",
     *     nullable = true
     * )
     * @var Module|null
     */
    protected $module;

    /**
     * @ORM\ManyToOne(targetEntity = "Navigation", inversedBy = "children")
     * @ORM\JoinColumn(
     *     name = "parent_id",
     *     referencedColumnName = "id",
     *     nullable = true
     * )
     * @var Navigation|null
     */
    protected $parent;

    /**
     * @ORM\Column(type = "integer", name = "sort_order", nullable = false, options = {"default" : 1})
     * @var string
     */
    protected $sortOrder;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = false, options = {"default" : "/"})
     * @var string
     */
    protected $url;

    /**
     * @ORM\OneToOne(targetEntity = "Permission")
     * @var Permission
     */
    protected $permission;

    /**
     * @ORM\Column(type = "datetime", name = "deleted_at", nullable = true)
     * @var DateTime|null
     */
    protected $deletedAt;


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


    public function getIcon(): ?string
    {
        return $this->icon;
    }


    public function getModule(): ?Module
    {
        return $this->module;
    }


    public function getParent(): ?Navigation
    {
        return $this->parent;
    }


    public function getPermission(): ?Permission
    {
        return $this->permission;
    }


    public function getPermissionCode(): ?string
    {
        return $this->getPermission()
            ? $this->getPermission()->getCode()
            : null;
    }


    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }


    public function getUrl(): string
    {
        return $this->url;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'icon' => $this->getIcon(),
            'moduleCode' => $this->getModule() ? $this->getModule()->getCode() : null,
            'url' => $this->getUrl(),
            'permissionCode' => $this->getPermissionCode(),
            'children' => ($this->children->count() > 0) ? $this->children->toArray() : [],
        ];
    }

}
