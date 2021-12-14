<?php

namespace App\Domain\Entities;

use App\Domain\Entities\EntityTranslationsAbstract;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name = "permissions_translations")
 */
class PermissionTranslation extends EntityTranslationsAbstract
{

    /**
     * @ORM\Column(type = "text", nullable = true)
     * @var string
     */
    protected $description;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity = "Permission", inversedBy = "translations")
     * @ORM\JoinColumn(
     *     name = "source_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     */
    protected $source;


    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function getSource(): Permission
    {
        return $this->source;
    }


    public function setDescription(?string $value): PermissionTranslation
    {
        $this->description = $value;

        return $this;
    }


    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'description' => $this->getDescription(),
        ]);
    }

}
