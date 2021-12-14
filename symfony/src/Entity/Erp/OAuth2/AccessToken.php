<?php

namespace App\Entity\Erp\OAuth2;

use App\Entity\Erp\User;
use App\Entity\IdentifiedAbstract;
use App\Traits\Entity\OAuth2\ExpirableTrait;
use App\Traits\Entity\OAuth2\RevocableTrait;
use App\Traits\Entity\OAuth2\ScopesTrait;
use App\Traits\Entity\TimestampableTrait;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.oauth_access_tokens")
 */
class AccessToken extends IdentifiedAbstract implements AccessTokenEntityInterface
{

    use AccessTokenTrait, ExpirableTrait, RevocableTrait, ScopesTrait, TimestampableTrait;

    /**
     * @ORM\Column(type = "string", name = "user_identifier", length = 128, nullable = true)
     *
     * @var string|null
     */
    protected $userIdentifier;

    /**
     * @ORM\ManyToOne(targetEntity = "Client")
     * @ORM\JoinColumn(
     *     nullable = false,
     *     onDelete = "CASCADE"
     * )
     *
     * @var Client|ClientEntityInterface
     */
    protected $client;

    /**
     * @var User|null
     */
    protected $user;


    /**
     * @return Client|ClientEntityInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    public function getIdentifier()
    {
        return $this->getId();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    public function setClient(ClientEntityInterface $value): self
    {
        $this->client = $value;

        return $this;
    }

    public function setIdentifier($identifier):self
    {
        // noop
        return $this;
    }

    /**
     * @param User|object|null $value
     * @return $this
     */
    public function setUser(?User $value): self
    {
        $this->user = $value;

        return $this;
    }

    public function setUserIdentifier($identifier): self
    {
        $this->userIdentifier = $identifier;

        return $this;
    }

}
