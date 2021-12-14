<?php

namespace App\Entity\Erp;

use App\Entity\EntityAbstract;
use App\Traits\Entity\TenantAsPrimaryAwareTrait;
use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.core_translations_custom")
 */
class TranslationCustom extends EntityAbstract
{

    use TenantAsPrimaryAwareTrait;

    /**
     * @ORM\Column(type = "text", nullable = false)
     *
     * @var string
     */
    protected $message;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity = "Translation", inversedBy = "translationCustom")
     *
     * @var Translation
     */
    protected $translation;

}
