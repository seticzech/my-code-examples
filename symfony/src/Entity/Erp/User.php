<?php

namespace App\Entity\Erp;

use App\Contract\Entity\ContainerAwareInterface;
use App\Contract\Entity\TenantAwareInterface;
use App\Doctrine\DBAL\Types\Citext;
use App\Entity\Erp\Acl\Role;
use App\Entity\Erp\Snm\UserToSocialNetwork;
use App\Entity\IdentifiedAbstract;
use App\Service\Erp\Acl\ActionService;
use App\Service\Erp\Acl\ResourceService;
use App\Traits\Entity\ApprovedTrait;
use App\Traits\Entity\RejectedTrait;
use App\Traits\Entity\SoftDeletableTrait;
use App\Traits\Entity\TenantAwareTrait;
use App\Traits\Entity\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.core_users",
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(name = "uniq_core_users_email_tenant_id",
 *             columns = {"email", "tenant_id"}
 *         )
 *     }
 * )
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class User extends IdentifiedAbstract implements TenantAwareInterface, UserInterface, UserEntityInterface, ContainerAwareInterface
{

    use ApprovedTrait,
        RejectedTrait,
        SoftDeletableTrait,
        TenantAwareTrait,
        TimestampableTrait;
    
    const ACL_RESOURCE_NAME = ResourceService::CODE_USER;
    const ACL_ACTION_UPDATE_ROLE = ActionService::CODE_USER_UPDATE_ROLE;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = true)
     *
     * @var string|null
     */
    protected $authCode;

    /**
     * @ORM\ManyToOne(targetEntity = "Company", inversedBy = "users", cascade = {"persist"})
     *
     * @Groups({"default", "oauth2"})
     * @SWG\Property(
     *     ref="#/definitions/Core_Company"
     * )
     *
     * @var Company|null
     */
    protected $company;

    /**
     * @ORM\Column(type = "citext", nullable = false)
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     *
     * @Groups({"default", "oauth2"})
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type = "string", name = "first_name", length = 48, nullable = true)
     *
     * @Assert\Length(max = "48")
     *
     * @Groups({"default", "oauth2"})
     *
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\ManyToMany(targetEntity = "UserGroup", mappedBy = "users")
     * 
     * @Groups({"userGroups"})
     *
     * @var ArrayCollection|UserGroup[]
     */
    protected $userGroups;

    /**
     * @ORM\Column(type = "string", name = "last_name", length = 48, nullable = true)
     *
     * @Assert\Length(max = "48")
     *
     * @Groups({"default", "oauth2"})
     *
     * @var string
     */
    protected $lastName;

    /**
     * @ORM\Column(type = "boolean", name = "is_client", options = {"default": false})
     *
     * @Groups({"default", "oauth2"})
     *
     * @var bool
     */
    protected $isClient;

    /**
     * @ORM\Column(type = "boolean", name = "is_company", options = {"default": false})
     *
     * @Groups({"default", "oauth2"})
     *
     * @var bool
     */
    protected $isCompany;

    /**
     * @ORM\Column(type = "boolean", name = "is_host", options = {"default": false})
     *
     * @Groups({"default", "oauth2"})
     *
     * @var bool
     */
    protected $isHost;

    /**
     * @ORM\Column(type = "string", length = 255, nullable = false)
     *
     * @Assert\Length(max = "255")
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type = "string", length = 16, nullable = true)
     *
     * @Assert\Length(max = "16")
     *
     * @Groups({"default", "oauth2"})
     *
     * @var string
     */
    protected $phone;

    /**
     * @ORM\ManyToMany(targetEntity = "App\Entity\Erp\Acl\Role")
     * @ORM\JoinTable(name = "bb_erp.acl_users_to_roles",
     *     joinColumns={@ORM\JoinColumn(name = "user_id", referencedColumnName = "id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name = "role_id", referencedColumnName = "id")}
     * )
     * 
     * @Groups({"default"})
     *
     * @var ArrayCollection|Role[]
     */
    protected $userRoles;

    /**
     * @ORM\OneToMany(targetEntity = "App\Entity\Erp\Snm\UserToSocialNetwork", mappedBy = "user")
     *
     * @Groups({"default", "oauth2"})
     *
     * @var UserToSocialNetwork[]
     */
    protected $userToSocialNetworks;

    /**
     * @ORM\Column(type = "string", name = "user_code", length = 64, nullable = true)
     *
     * @Assert\Length(max = "255")
     *
     * @Groups({"default", "oauth2"})
     *
     * @var string
     */
    protected $userCode;


    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->isApproved = false;
        $this->isClient = false;
        $this->isCompany = false;
        $this->isHost = false;
        $this->isRejected = false;
    }
    
    /**
     * @return UserGroup[]|ArrayCollection
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
            $userGroup->addUser($this);
        }

        return $this;
    }
    
    public function removeUserGroup(UserGroup $userGroup): self
    {
        if ($this->userGroups->contains($userGroup)) {
            $this->userGroups->removeElement($userGroup);
            $userGroup->removeUser($this);
        }

        return $this;
    }
    
    /**
     * @return Role[]|ArrayCollection
     */
    public function getUserRoles()
    {
        return $this->userRoles->getValues();
    }
    
    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
        }

        return $this;
    }
    
    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
        }

        return $this;
    }
    
    public function hasDefaultUserRole()
    {
        $this->userRoles->exists(function($key, $role) {
            return $role->getIsDefault();
        });
    }
    
    public function clearAuthCode(): self
    {
        $this->authCode = null;

        return $this;
    }

    public function eraseCredentials()
    {
        // noop
    }

    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    
    public function getFullName(): ?string
    {
        return $this->getFirstName()." ".$this->getLastName();
    }

    public function getIdentifier(): ?string
    {
        return $this->getId();
    }

    public function getIsClient(): bool
    {
        return $this->isClient;
    }

    public function getIsCompany(): bool
    {
        return $this->isCompany;
    }

    public function getIsHost(): bool
    {
        return $this->isHost;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->userRoles->map(function(Role $role) {
            return $role->getCode();
        });
    }

    public function getSalt(): ?string
    {
        return md5(time());
    }

    public function getUserCode(): ?string
    {
        return $this->userCode;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    /**
     * @return UserToSocialNetwork[]
     */
    public function getUserToSocialNetworks()
    {
        return $this->userToSocialNetworks;
    }

    public function setAuthCode(?string $value): self
    {
        $this->authCode = $value;

        return $this;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function setEmail(string $value): self
    {
        $this->email = $value;

        return $this;
    }

    public function setFirstName(string $value = null): self
    {
        $this->firstName = $value;

        return $this;
    }

    public function setIsClient(bool $value): self
    {
        $this->isClient = $value;

        return $this;
    }

    public function setIsCompany(bool $value): self
    {
        $this->isCompany = $value;

        return $this;
    }

    public function setIsHost(bool $value): self
    {
        $this->isHost = $value;

        return $this;
    }

    public function setLastName(string $value = null): self
    {
        $this->lastName = $value;

        return $this;
    }

    public function setPassword(string $value): self
    {
        $this->password = $value;

        return $this;
    }

    public function setPhone(?string $value): self
    {
        $this->phone = $value;

        return $this;
    }

    public function setUserCode(string $code = null): self
    {
        $this->userCode = $code;

        return $this;
    }
    
    /**
     * @Groups({"acl"})
     * 
     * @return bool
     */
    public function getIsAllowedToUpdateRoles(): bool
    {
        return $this->isAllowed(self::ACL_ACTION_UPDATE_ROLE);
    }

}
