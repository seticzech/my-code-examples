<?php

namespace App\Domain\Entities;

use App\Traits\Domain\Entities\SoftDeleteable;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "languages")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Language extends IdentifiedAbstract
{

    use SoftDeleteable, Timestamps;

    const DIRECTION_LTR = 'ltr';
    const DIRECTION_RTL = 'rtl';

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $adverb;

    /**
     * @ORM\Column(type = "string", length = 10, nullable = false)
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type = "string", length = 2, nullable = true)
     * @var string
     */
    protected $flag;

    /**
     * @ORM\Column(type = "string", name = "iso_code_2", length = 2, nullable = true)
     * @var string
     */
    protected $isoCode2;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $locale;

    /**
     * @ORM\Column(type = "integer", name = "plurals_count", nullable = false, options = {"default" : 1})
     * @var int
     */
    protected $pluralsCount;

    /**
     * @ORM\Column(type = "json", name = "plurals_rules", nullable = true)
     * @var array
     */
    protected $pluralsRules;

    /**
     * @ORM\Column(type = "string", length = 10, nullable = false, options = {"default" : "ltr"})
     * @var string
     */
    protected $direction;

    /**
     * @ORM\Column(type = "boolean", name = "is_active", options = {"default" : false})
     * @var bool
     */
    protected $isActive;

    /**
     * @ORM\Column(type = "integer", name = "sort_order", nullable = false, options = {"default" : 1})
     * @var int
     */
    protected $sortOrder;


    public function __construct(string $name, int $sortOrder)
    {
        $this->name = $name;
        $this->pluralsCount = 1;
        $this->pluralsRules = (object) [];
        $this->direction = self::DIRECTION_LTR;
        $this->isActive = false;
        $this->sortOrder = $sortOrder;
    }


    public function getAdverb(): string
    {
        return $this->adverb;
    }


    public function getCode(): string
    {
        return $this->code;
    }


    public function getDirection(): string
    {
        return $this->direction;
    }


    public function getFlag(): ?string
    {
        return $this->flag;
    }


    public function getIsActive(): bool
    {
        return $this->isActive;
    }


    public function getIsoCode2(): ?string
    {
        return $this->isoCode2;
    }


    public function getLocale(): string
    {
        return $this->locale;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getPluralsCount(): int
    {
        return $this->pluralsCount;
    }


    public function getPluralsRules(): array
    {
        return $this->pluralsRules;
    }


    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }


    public function setAdverb(string $value): Language
    {
        $this->adverb = $value;

        return $this;
    }


    public function setCode(string $value): Language
    {
        $this->code = $value;

        return $this;
    }


    public function setDirection(string $value): Language
    {
        $this->direction = $value;

        return $this;
    }


    public function setIsActive(bool $value): Language
    {
        $this->isActive = $value;

        return $this;
    }


    public function setLocale(string $value): Language
    {
        $this->locale = $value;

        return $this;
    }


    public function setPluralsCount(int $value): Language
    {
        $this->pluralsCount = $value;

        return $this;
    }


    public function setPluralsRules(array $value): Language
    {
        $this->pluralsRules = $value;

        return $this;
    }


    public function setSortOrder(int $value): Language
    {
        $this->sortOrder = $value;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'adverb' => $this->getAdverb(),
            'code' => $this->getCode(),
            'flag' => $this->getFlag(),
            'locale' => $this->getLocale(),
            'isoCode2' => $this->getIsoCode2(),
            'pluralsCount' => $this->getPluralsCount(),
            'pluralsRules' => $this->getPluralsRules(),
            'direction' => $this->getDirection(),
            'deletedAt' => $this->getDeletedAt(),
        ];
    }

}
