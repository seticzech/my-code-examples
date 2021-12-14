<?php

namespace App\Entity\Erp;

use App\Contract\Entity\ContainerAwareInterface;
use App\Entity\Erp\App\AppSettingsOptions;
use App\Entity\Erp\AppSettings;
use App\Entity\IdentifiedAbstract;
use App\Service\Erp\Acl\ActionService;
use App\Service\Erp\Acl\ResourceService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.core_apps")
 */
class App extends IdentifiedAbstract implements ContainerAwareInterface
{

    const ACL_RESOURCE_NAME = ResourceService::CODE_APP;
    const ACL_ACTION_UPDATE_SETTINGS = ActionService::CODE_APP_UPDATE_SETTINGS;
    
    /**
     * @ORM\Column(type = "string", length = 32, nullable = false, unique = true)
     *
     * @Groups({"default"})
     *
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     *
     * @Groups({"default"})
     *
     * @var string
     */
    protected $name;
    
    /**
    * @ORM\OneToMany(targetEntity="AppSettings", mappedBy="app")
    * 
    *  @var AppSettings[]|ArrayCollection
    */
    protected $settings;


    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setCode(string $value): self
    {
        $this->code = $value;

        return $this;
    }

    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }
    
    public function getSettings(): ?AppSettings
    {
        if ($this->settings) {
            if (is_array($this->settings)) {
                return reset($this->settings);
            } else if (!$this->settings->isEmpty()) {
                return $this->settings->first();
            }
        }
        
        return null;
    }
    
    /**
    * @Groups({"default"})
    * @SWG\Property(
    *     type="object",
    *     @SWG\Property(
    *          property="homepageImageId", 
    *          type="UUID", 
    *          example="7ce9f0b5-d805-452d-adc3-e036678fb10a", 
    *          description="Blackbelt homepage image file id"
    *      ),
    *      @SWG\Property(
    *          property="isHomepageHeroAndCourseShown", 
    *          type="boolean", 
    *          description="Blackbelt show hero section and current course simultaneously"
    *      )
    * )
    * 
    * @return array
    */
    public function getOptions()
    {
        return $this->getSettings() 
            ? $this->getSettings()->getOptions() 
            : AppSettingsOptions::getDefaultOptions($this->getCode());
    }

    /**
     * @Groups({"acl"})
     * 
     * @return bool
     */
    public function getIsAllowedToUpdateSettings(): bool
    {
        return $this->isAllowed(self::ACL_ACTION_UPDATE_SETTINGS);
    }

}
