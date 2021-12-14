<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Expr\AssignOp\Mod;


/**
 * @ORM\Entity
 * @ORM\Table(name = "translations",
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(name = "uniq_translations_lang_id_code",
 *             columns = {"language_id", "code"},
 *             options = {"where": "((context IS NULL) AND (module_id IS NULL))"}
 *         ),
 *         @ORM\UniqueConstraint(name = "uniq_translations_lang_id_context_code",
 *             columns = {"language_id", "context", "code"},
 *             options = {"where": "((context IS NOT NULL) AND (module_id IS NULL))"}
 *         ),
 *         @ORM\UniqueConstraint(name = "uniq_translations_lang_id_module_id_code",
 *             columns = {"language_id", "module_id", "code"},
 *             options = {"where": "((context IS NULL) AND (module_id IS NOT NULL))"}
 *         ),
 *         @ORM\UniqueConstraint(name = "uniq_translations_lang_id_module_id_context_code",
 *             columns = {"language_id", "module_id", "context", "code"},
 *             options = {"where": "((context IS NOT NULL) AND (module_id IS NOT NULL))"}
 *         )
 *     }
 * )
 */
class Translation extends IdentifiedAbstract
{

    /**
     * @ORM\Column(type = "string", length = 128, nullable = true)
     * @var string
     */
    protected $context;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = false)
     * @var string
     */
    protected $code;

    /**
     * @ORM\ManyToOne(targetEntity = "Language")
     * @ORM\JoinColumn(
     *     name = "language_id",
     *     referencedColumnName = "id",
     *     nullable = false
     * )
     * @var Language
     */
    protected $language;

    /**
     * @ORM\ManyToOne(targetEntity = "Module")
     * @ORM\JoinColumn(
     *     name = "module_id",
     *     referencedColumnName = "id",
     *     nullable = true
     * )
     * @var Module
     */
    protected $module;

    /**
     * @ORM\Column(type = "text", nullable = false)
     * @var string
     */
    protected $message;

    /**
     * @ORM\Column(type = "string", length = 256, nullable = true)
     * @var string
     */
    protected $description;


    public function getCode(): string
    {
        return $this->code;
    }


    public function getContext(): string
    {
        return $this->context;
    }


    public function getMessage(): string
    {
        return $this->message;
    }


    public function getModule(): ?Module
    {
        return $this->module;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'module_code' => $this->getModule() ? $this->getModule()->getCode() : null,
            'context' => $this->getContext(),
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ];
    }

}
