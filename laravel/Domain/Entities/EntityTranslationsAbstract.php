<?php

namespace App\Domain\Entities;

use App\Base\Domain\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\MappedSuperclass
 */
abstract class EntityTranslationsAbstract extends Entity
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity = "App\Domain\Entities\Language")
     * @ORM\JoinColumn(
     *     name = "language_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var Language
     */
    protected $language;

    /**
     * MUST be overrided in child class with association definition:
     * (set proper value of 'targetEntity' and add @ before 'ORM')
     *
     * ORM\Id
     * ORM\ManyToOne(targetEntity = "Translated_Entity_Name", inversedBy = "translations")
     * ORM\JoinColumn(
     *     name = "source_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     */
    protected $source;


    /**
     * EntityTranslationsAbstract constructor
     *
     * @param IdentifiedTranslatableAbstract $source instance of the translated entity
     * @param Language|null $language
     */
    public function __construct($source, Language $language = null)
    {
        $this->language = $language;
        $this->source = $source;
    }


    public function clearSource(): self
    {
        $this->source = null;

        return $this;
    }


    public function getLanguage(): Language
    {
        return $this->language;
    }


    public abstract function getSource();


    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }


    public function setSource($source): self
    {
        $this->source = $source;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'language_id' => $this->getLanguage()->getId(),
            'language_code' => $this->getLanguage()->getCode(),
        ];
    }

}
