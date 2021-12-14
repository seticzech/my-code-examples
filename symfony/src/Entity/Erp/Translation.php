<?php

namespace App\Entity\Erp;

use App\Entity\IdentifiedAbstract;
use Doctrine\ORM\Mapping as ORM;

/**
 * Unique constraint "uniq_core_translations_language_id_module_id_context_code" has only two columns
 * because Doctrine does not support COALESCE on columns and that columns are invisible for Doctrine.
 *
 * In fact constraint has this format:
 *
 * CREATE UNIQUE INDEX uniq_core_translations_language_id_module_id_context_code ON bb_erp.core_translations
 * (language_id, COALESCE(module_id, '00000000-0000-0000-0000-000000000000'), COALESCE(context, ''), code);
 *
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.core_translations",
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(name = "uniq_core_translations_language_id_module_id_context_code",
 *             columns = {"language_id", "code"}
 *         )
 *     }
 * )
 */
class Translation extends IdentifiedAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity = "App")
     * @ORM\JoinColumn(nullable = false)
     *
     * @var App
     */
    protected $app;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = false)
     *
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = true)
     *
     * @var string
     */
    protected $context;

    /**
     * @ORM\Column(type = "string", length = 256, nullable = true)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity = "Language")
     * @ORM\JoinColumn(nullable = false)
     *
     * @var Language
     */
    protected $language;

    /**
     * @ORM\Column(type = "text", nullable = false)
     *
     * @var string
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity = "Module")
     *
     * @var Module|null
     */
    protected $module;

    /**
     * @ORM\OneToOne(targetEntity = "TranslationCustom", mappedBy = "translation")
     *
     * @var TranslationCustom|null
     */
    protected $translationCustom;


    public function getApp(): ?App
    {
        return $this->app;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function getTranslationCustom(): ?TranslationCustom
    {
        return $this->translationCustom;
    }

    public function setApp(App $app): self
    {
        $this->app = $app;

        return $this;
    }

    public function setCode(string $value): self
    {
        $this->code = $value;

        return $this;
    }

    public function setContext(string $value): self
    {
        $this->context = $value;

        return $this;
    }

    public function setDescription(?string $value): self
    {
        $this->description = $value;

        return $this;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setMessage(string $value): self
    {
        $this->message = $value;

        return $this;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function setTranslationCustom(?TranslationCustom $translationCustom): self
    {
        $this->translationCustom = $translationCustom;

        return $this;
    }

}
