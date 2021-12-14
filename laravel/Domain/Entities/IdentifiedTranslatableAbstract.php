<?php

namespace App\Domain\Entities;

use App\Base\Domain\Entity;
use App\Contracts\TranslatableEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


abstract class IdentifiedTranslatableAbstract extends IdentifiedAbstract implements TranslatableEntityInterface
{

    /**
     * MUST be overrided in child class with association definition
     *
     * @var ArrayCollection
     */
    protected $translations;


    public function addTranslation(EntityTranslationsAbstract $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setSource($this);
        }

        return $this;
    }


    public function clearTranslations(): self
    {
        foreach ($this->translations as $translation) {
            $this->removeTranslation($translation);
        }

        return $this;
    }


    /**
     * @return ArrayCollection|Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }


    public function hasTranslation(EntityTranslationsAbstract $translation): bool
    {
        return $this->translations->contains($translation);
    }


    public function removeTranslation(EntityTranslationsAbstract $translation): self
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
            $translation->clearSource();
        }

        return $this;
    }


    public function setTranslations(iterable $collection): self
    {
        $this->clearTranslations();

        foreach ($collection as $item) {
            $this->addTranslation($item);
        }

        return $this;
    }

}
