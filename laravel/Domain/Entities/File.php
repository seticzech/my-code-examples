<?php

namespace App\Domain\Entities;

use App\Base\Domain\Entity;
use App\Traits\Domain\Entities\SoftDeleteable;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;


/**
 * @ORM\Entity
 * @ORM\Table(name = "files")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class File extends IdentifiedAbstract
{

    use SoftDeleteable, Timestamps;

    /**
     * @ORM\Column(type = "string", name = "assigned_to", length = 64, nullable = true)
     * @var string
     */
    protected $assignedTo;

    /**
     * @ORM\Column(type = "integer", name = "assigned_id", nullable = true)
     * @var int
     */
    protected $assignedId;

    /**
     * @ORM\Column(type = "string", length = 32, nullable = false, unique = true)
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type = "string", length = 250, nullable = false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", name = "mime_type", length = 64, nullable = false)
     * @var string
     */
    protected $mimeType;

    /**
     * @ORM\Column(type = "string", length = 250, nullable = true)
     * @var string
     */
    protected $path;

    /**
     * @ORM\Column(type = "integer", nullable = false)
     * @var int
     */
    protected $size;

    /**
     * @ORM\Column(type = "boolean", name = "partial_upload", nullable = false)
     * @var int
     */
    protected $partialUpload;

    /**
     * @ORM\ManyToOne(targetEntity = "User")
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(type = "datetime", name = "uploaded_at", nullable = true)
     * @var DateTime|null
     */
    protected $uploadedAt;


    public function __construct(User $user, string $name, string $mimeType, int $size)
    {
        $this->code = uniqid(uniqid());
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->partialUpload = false;
        $this->size = $size;
        $this->user = $user;
    }


    public function assign(string $to, int $id): File
    {
        $this->assignedTo = $to;
        $this->assignedId = $id;

        return $this;
    }


    public function assignEntity(IdentifiedAbstract $entity)
    {
        $this->assignedTo = $entity->getEntityMetaData()->getTableName();
        $this->assignedId = $entity->getId();

        return $this;
    }


    public function getCode(): string
    {
        return $this->code;
    }


    public function getFullName(): string
    {
        return $this->getPath()
            ? $this->getPath() . DIRECTORY_SEPARATOR . $this->getName()
            : $this->getName();
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getMimeType(): string
    {
        return $this->mimeType;
    }


    public function getPath(): ?string
    {
        return $this->path;
    }


    public function getRealName(): string
    {
        return $this->getPath()
            ? $this->getPath() . DIRECTORY_SEPARATOR . $this->getCode()
            : $this->getCode();
    }


    public function getSize(): int
    {
        return $this->size;
    }


    public function getUploadedAt(): ?DateTime
    {
        return $this->uploadedAt;
    }


    public function getUser(): User
    {
        return $this->user;
    }


    public function setName(string $value): File
    {
        $this->name = $value;

        return $this;
    }


    public function setPartialUpload(int $value): File
    {
        $this->partialUpload = $value;

        return $this;
    }


    public function setPath(string $value): File
    {
        $this->path = $value;

        return $this;
    }


    public function setUploadedAt(DateTime $value): File
    {
        $this->uploadedAt = $value;

        return $this;
    }


    public function unassign(): File
    {
        $this->assignedId = null;
        $this->assignedTo = null;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'mimeType' => $this->getMimeType(),
            'path' => $this->getPath(),
            'size' => $this->getSize(),
            'deletedAt' => $this->getDeletedAt(),
            'uploadedAt' => $this->getUploadedAt(),
            'url' => url("/api/file/{$this->getId()}/download"),
        ];
    }

}
