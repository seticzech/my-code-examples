<?php

namespace App\Domain\Entities;

use App\Domain\Entities\EntityTranslationsAbstract;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name = "roles_translations")
 */
class RoleTranslation extends EntityTranslationsAbstract
{

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity = "Role", inversedBy = "translations")
     * @ORM\JoinColumn(
     *     name = "source_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     */
    protected $source;


    public function getName(): string
    {
        return $this->name;
    }


    public function getSource(): Role
    {
        return $this->source;
    }

    public function setName(string $value): RoleTranslation
    {
        $this->name = $value;

        return $this;
    }


    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'name' => $this->getName(),
        ]);
    }

}
