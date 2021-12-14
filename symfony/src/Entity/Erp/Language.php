<?php

namespace App\Entity\Erp;

use App\Entity\IdentifiedAbstract;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.core_languages")
 */
class Language extends IdentifiedAbstract
{

    //use TenantAware;

    const DIRECTION_LTR = 'ltr';
    const DIRECTION_RTL = 'rtl';

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = "64")
     *
     * @Groups({"default", "anon"})
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", name = "native_name", length = 48, nullable = false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = "48")
     *
     * @Groups({"default", "anon"})
     *
     * @var string
     */
    protected $nativeName;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = "64")
     *
     * @Groups({"default", "anon"})
     *
     * @var string
     */
    protected $adverb;

    /**
     * @ORM\Column(type = "string", name = "iso_639_1", length = 2, nullable = false)
     *
     * @Assert\Length(max = "2")
     *
     * @Groups({"default", "anon"})
     *
     * @var string
     */
    protected $iso_639_1;

    /**
     * @ORM\Column(type = "string", name = "iso_639_2_b", length = 3, nullable = true)
     *
     * @Assert\Length(max = "3")
     *
     * @Groups({"default", "anon"})
     *
     * @var string|null
     */
    protected $iso_639_2_b;

    /**
     * @ORM\Column(type = "string", length = 24, nullable = false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = "24")
     *
     * @Groups({"default", "anon"})
     *
     * @var string
     */
    protected $locale;

//    /**
//     * @ORM\Column(type = "integer", name = "plurals_count", nullable = false, options = {"default" : 1})
//     * @var int
//     */
//    protected $pluralsCount;
//
//    /**
//     * @ORM\Column(type = "json", name = "plurals_rules", nullable = true)
//     * @var array
//     */
//    protected $pluralsRules;

    /**
     * @ORM\Column(type = "string", length = 10, nullable = false, options = {"default" : "ltr"})
     *
     * @Assert\Length(max = "10")
     * @Assert\Choice({Language::DIRECTION_LTR, Language::DIRECTION_RTL})
     *
     * @Groups({"default", "anon"})
     *
     * @var string
     */
    protected $direction;

    /**
     * @ORM\Column(type = "boolean", name = "is_active", options = {"default" : false})
     *
     * @var bool
     */
    protected $isPublished;


    public function __construct()
    {
//        $this->name = $name;
//        $this->pluralsCount = 1;
//        $this->pluralsRules = (object) [];
        $this->direction = self::DIRECTION_LTR;
        $this->isPublished = false;
    }

    public function getAdverb(): ?string
    {
        return $this->adverb;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function getIso6391(): ?string
    {
        return $this->iso_639_1;
    }

    public function getIso6392b(): ?string
    {
        return $this->iso_639_2_b;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNativeName(): ?string
    {
        return $this->nativeName;
    }

//    public function getPluralsCount(): int
//    {
//        return $this->pluralsCount;
//    }
//
//    public function getPluralsRules(): array
//    {
//        return $this->pluralsRules;
//    }
//

    public function setAdverb(string $value): self
    {
        $this->adverb = $value;

        return $this;
    }

    public function setDirection(string $value): self
    {
        $this->direction = $value;

        return $this;
    }

    public function setIso6391(string $value): self
    {
        $this->iso_639_1 = $value;

        return $this;
    }

    public function setIso6392b(?string $value = null): self
    {
        $this->iso_639_2_b = $value;

        return $this;
    }

    public function setIsPublished(bool $value): self
    {
        $this->isPublished = $value;

        return $this;
    }

    public function setLocale(string $value): self
    {
        $this->locale = $value;

        return $this;
    }

    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    public function setNativeName(string $value): self
    {
        $this->nativeName = $value;

        return $this;
    }

//    public function setPluralsCount(int $value): self
//    {
//        $this->pluralsCount = $value;
//
//        return $this;
//    }
//
//    public function setPluralsRules(array $value): self
//    {
//        $this->pluralsRules = $value;
//
//        return $this;
//    }

    public function fromArray(array $data, array $validKeys)
    {

    }

}
