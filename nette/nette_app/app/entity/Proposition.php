<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nette;
use Nettrine\ORM\Entity\Attributes\Id;


/**
 * @ORM\Entity
 * @ORM\Table(name = "proposition")
 *
 * @method getName()
 * @method getProjectId()
 */
class Proposition
{

    use Id;
    use Immutable;


    /**
     * @ORM\Column(type = "integer", name = "project_id")
     */
    protected $projectId;


    /**
     * @ORM\Column(type = "string")
     */
    protected $name;


    /**
     * @ORM\Column(type = "string", name = "proposition_id", length = 40)
     */
    protected $propositionId;


    /**
     * @ORM\Column(type = "string", name = "product_id", length = 40)
     */
    protected $productId;


    /**
     * @ORM\Column(type = "string", name = "article_elk_id", length = 40)
     */
    protected $articleElkId;


    /**
     * @ORM\Column(type = "string", name = "article_gas_id", length = 40)
     */
    protected $articleGasId;


    /**
     * @ORM\Column(type = "integer")
     */
    protected $duration;


    /**
     * @ORM\Column(type = "integer", name = "customer_type")
     */
    protected $customerType;


    /**
     * @ORM\Column(type = "boolean", name = "active_at", options={"default" : false})
     */
    protected $activeAt;


    /**
     * @ORM\Column(type = "date", name = "active_from", nullable = true)
     */
    protected $activeFrom;


    /**
     * @ORM\Column(type = "date", name = "active_to", nullable = true)
     */
    protected $activeTo;


    /**
     * @ORM\Column(type = "datetime", name = "created_at")
     */
    protected $createdAt;


    /**
     * @ORM\Column(type = "datetime", name = "modified_at", nullable = true)
     * @var DateTime
     */
    protected $modifiedAt;


    /**
     * @ORM\Column(type = "boolean", options={"default" : false})
     */
    protected $deleted;



    public function __construct(string $name)
    {
        $this->name = $name;
        $this->createdAt = new DateTime();
    }



    public function setProjectId($value)
    {
        $this->projectId = $value;

        return $this;
    }



    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }



    public function setPropositionId($value)
    {
        $this->propositionId = $value;

        return $this;
    }



    public function setProductId($value)
    {
        $this->productId = $value;

        return $this;
    }



    public function setArticleElkId($value)
    {
        $this->articleElkId = $value;

        return $this;
    }



    public function setArticleGasId($value)
    {
        $this->articleGasId = $value;

        return $this;
    }



    public function setDuration($value)
    {
        $this->duration = $value;

        return $this;
    }



    public function setCustomerType($value)
    {
        $this->customerType = $value;

        return $this;
    }



    public function setActiveAt($value)
    {
        $this->activeAt = (bool) $value;

        return $this;
    }



    public function setModifiedAt(DateTime $value = null)
    {
        if (!$value) {
            $value = new DateTime();
        }

        $this->modifiedAt = $value;

        return $this;
    }



    public function setDeleted($value)
    {
        $this->deleted = (bool) $value;

        return $this;
    }



    public function serialize()
    {
        return [
            'id' => $this->id,
            'project_id' => $this->projectId,
            'name' => $this->name,
            'proposition_id' => $this->propositionId,
            'product_id' => $this->productId,
            'article_elk_id' => $this->articleElkId,
            'article_gas_id' => $this->articleGasId,
            'duration' => $this->duration,
            'customer_type' => $this->customerType,
            'active_at' => $this->activeAt,
            'active_from' => $this->activeFrom,
            'active_to' => $this->activeTo,
            'created_at' => $this->createdAt->format('Y-m-d H:i.s'),
            'modified_at' => $this->modifiedAt ? $this->modifiedAt->format('Y-m-d H:i.s') : null,
            'deleted' => $this->deleted,
        ];
    }


}
