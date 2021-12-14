<?php

namespace App\Entity\Erp\OAuth2;

use App\Entity\IdentifiedAbstract;
use App\Traits\Entity\OAuth2\RevocableTrait;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.oauth_refresh_tokens")
 */
class RefreshToken extends IdentifiedAbstract implements RefreshTokenEntityInterface
{

    use RevocableTrait;

    /**
     * @ORM\ManyToOne(targetEntity = "AccessToken")
     * @ORM\JoinColumn(
     *     nullable = false,
     *     onDelete = "SET NULL"
     * )
     *
     * @var AccessToken|AccessTokenEntityInterface
     */
    protected $accessToken;

    /**
     * @ORM\Column(type = "datetime_immutable", name = "expiry")
     *
     * @var DateTimeImmutable
     */
    protected $expiryDateTime;

    /**
     * @return AccessToken|AccessTokenEntityInterface
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getExpiryDateTime(): DateTimeImmutable
    {
        return $this->expiryDateTime;
    }

    public function getIdentifier(): string
    {
        return $this->getId();
    }

    public function setAccessToken(AccessTokenEntityInterface $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function setExpiryDateTime(DateTimeImmutable $dateTime): self
    {
        $this->expiryDateTime = $dateTime;

        return $this;
    }

    public function setIdentifier($identifier): self
    {
        // noop
        return $this;
    }

}
