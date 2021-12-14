<?php

namespace App\Domain\Entities;

use App\Exceptions\InvalidArgumentException;
use App\Security\Passwords\Hasher;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Passport\HasApiTokens;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;
use LaravelDoctrine\ORM\Notifications\Notifiable;


/**
 * @ORM\Entity
 * @ORM\Table(name = "users")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt", timeAware = false)
 */
class User extends IdentifiedAbstract implements AuthenticatableContract, CanResetPasswordContract
{

    use CanResetPassword, Timestamps, Notifiable, HasApiTokens;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false, unique = true)
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false)
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = false, unique = true)
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type = "string", name = "first_name", length = 48, nullable = true)
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(type = "string", name = "last_name", length = 48, nullable = true)
     * @var string
     */
    protected $lastName;

    /**
     * @ORM\Column(type = "datetime", name = "deleted_at", nullable = true)
     * @var DateTime|null
     */
    protected $deletedAt;

    /**
     * @ORM\ManyToMany(targetEntity = "Role")
     * @ORM\JoinTable(name = "users_to_roles",
     *     joinColumns={@ORM\JoinColumn(name = "user_id", referencedColumnName = "id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name = "role_id", referencedColumnName = "id")}
     * )
     * @var ArrayCollection
     */
    protected $roles;

    /**
     * Laravel passport
     *
     * @ORM\Column(name = "remember_token", type = "string", length = 64, nullable = true)
     * @var string
     */
    protected $rememberToken;

    protected $tokens;


    public function __construct(string $email)
    {
        $this->email = $email;
        $this->roles = new ArrayCollection();
    }


    public function addRole(Role $role): User
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }


    public function clearRoles(): User
    {
        $this->roles->clear();

        return $this;
    }


    public function generateUsername(int $suffixLength = 4): string
    {
        $base = substr(array_first(explode('@', $this->email)), 0, 5);

        $rMin = (int) str_pad('', $suffixLength, '1');
        $rMax = (int) str_pad('', $suffixLength, '9');

        return $base . rand($rMin, $rMax);
    }


    /**
     * Get the column name for the primary key
     * Laravel passport
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }


    /**
     * Get the unique identifier for the user.
     * Laravel passport
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        $name = $this->getAuthIdentifierName();

        return $this->{$name};
    }


    /**
     * Get the password for the user.
     * Laravel passport
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }


    public function getEmail(): string
    {
        return $this->email;
    }


    public function getFirstName(): ?string
    {
        return $this->firstName;
    }


    /**
     * Key user identifier
     * Laravel passport
     */
    public function getKey(): int
    {
        return $this->getId();
    }


    public function getLastName(): ?string
    {
        return $this->lastName;
    }


    /**
     * Get the token value for the "remember me" session.
     * Laravel passport
     */
    public function getRememberToken(): string
    {
        return $this->rememberToken;
    }


    /**
     * Get the column name for the "remember me" token.
     * Laravel passport
     */
    public function getRememberTokenName(): string
    {
        return 'rememberToken';
    }


    /**
     * @return ArrayCollection|Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function hasRole(Role $role): bool
    {
        return $this->roles->contains($role);
    }


    public function isSuperAdmin(): bool
    {
        foreach ($this->getRoles() as $role) {
            if ($role->getIsSuperAdmin()) {
                return true;
            }
        }

        return false;
    }


    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->username) {
            $this->username = $this->generateUsername();
        }
    }


    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        if (!$this->username) {
            $this->username = $this->generateUsername();
        }
    }


    public function removeRole(Role $role): User
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }


    public function setEmail(string $value): User
    {
        $this->email = $value;

        return $this;
    }


    public function setFirstName(?string $value): User
    {
        $this->firstName = $value;

        return $this;
    }


    public function setLastName(?string $value): User
    {
        $this->lastName = $value;

        return $this;
    }


    /**
     * @param string $hash
     * @return User
     * @throws InvalidArgumentException
     */
    public function setPassword(string $hash): User
    {
        if (Hasher::isPlaintext($hash)) {
            throw new InvalidArgumentException('Unknown encryption algorithm used, do not assign plaintext passwords to user directly.');
        }

        $this->password = $hash;

        return $this;
    }


    /**
     * Set the token value for the "remember me" session.
     * Laravel passport
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->rememberToken = $value;
    }


    public function setRoles(iterable $collection): User
    {
        $this->roles->clear();

        foreach ($collection as $item) {
            $this->addRole($item);
        }

        return $this;
    }


    public function setUsername(?string $value): User
    {
        if (!$value && !$this->username) {
            $value = $this->generateUsername();
        }

        if ($value) {
            $this->username = $value;
        }

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            //'roles' => array_values($this->getRoles()->toArray()),
        ];
    }

}
