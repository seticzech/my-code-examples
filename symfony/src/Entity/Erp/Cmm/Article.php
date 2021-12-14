<?php

namespace App\Entity\Erp\Cmm;

use App\Contract\Entity\TenantAwareInterface;
use App\Contract\Entity\UserAwareInterface;
use App\Entity\Erp\Mlm\File;
use App\Entity\IdentifiedAbstract;
use App\Traits\Entity\PublishedTrait;
use App\Traits\Entity\SoftDeletableTrait;
use App\Traits\Entity\TenantAwareTrait;
use App\Traits\Entity\TimestampableTrait;
use App\Traits\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.cmm_articles")
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class Article extends IdentifiedAbstract implements TenantAwareInterface, UserAwareInterface
{

    use PublishedTrait,
        SoftDeletableTrait,
        TenantAwareTrait,
        TimestampableTrait,
        UserAwareTrait;

    /**
     * @ORM\Column(type = "string", length = 256, nullable = false)
     *
     * @Groups({"default"})
     *
     * @var string
     */
    protected $title;
    
    /**
     * @ORM\Column(type = "json_array", options = {"jsonb": true, "default": "{}"})
     *
     * @Groups({"default"})
     * @SWG\Property(type="object")
     *
     * @var array
     */
    protected $content;
    
    /**
     * @ORM\ManyToOne(targetEntity = "App\Entity\Erp\Mlm\File")
     * @ORM\JoinColumn(nullable = true)
     *
     * @Groups({"default"})
     *
     * @var File
     */
    protected $image;
    
    
    public function __construct()
    {
        $this->isPublished = false;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $value): self
    {
        $this->title = $value;

        return $this;
    }
    
    public function getContent(): ?array
    {
        return $this->content;
    }
    
    public function setContent(array $value): self
    {
        $this->content = $value;

        return $this;
    }
    
    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image = null): self
    {
        $this->image = $image;
        
        return $this;
    }

}
