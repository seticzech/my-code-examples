<?php

namespace App\Entity\Erp\OAuth2;

use App\Bundle\OAuth2Bundle\Model\RedirectUri;
use App\Entity\IdentifiedAbstract;
use App\Exception\InvalidArgumentException;
use App\Traits\Entity\OAuth2\RevocableTrait;
use App\Traits\Entity\OAuth2\ScopesTrait;
use App\Traits\Entity\TenantOptionalAwareTrait;
use App\Traits\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.oauth_clients")
 */
class Client extends IdentifiedAbstract implements ClientEntityInterface
{

    use RevocableTrait, ScopesTrait, TenantOptionalAwareTrait, TimestampableTrait;

    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    const GRANT_TYPE_PASSWORD = 'password';

    private const GRANT_TYPES = [
        self::GRANT_TYPE_AUTHORIZATION_CODE,
        self::GRANT_TYPE_PASSWORD
    ] ;

    /**
     * @ORM\Column(type = "string", name = "grant_type", length = 20, nullable = false)
     *
     * @var string
     */
    protected $grantType;

    /**
     * @ORM\Column(type = "string", length = 64, nullable = true)
     *
     * @Groups({"default"})
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", length = 128, nullable = false)
     *
     * @Groups({"default"})
     *
     * @var string
     */
    protected $secret;

    /**
     * @ORM\Column(type = "oauth2_redirect_uri", nullable = true)
     *
     * @var RedirectUri[]
     */
    protected $redirectUris;

    /**
     * @ORM\Column(type = "boolean", name = "is_active", options = {"default" : true})
     *
     * @var bool
     */
    protected $isActive;


    public function __construct()
    {
        $this->isActive = true;
        $this->redirectUris = [];
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function getIdentifier()
    {
        return $this->getId();
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|string[]
     */
    public function getRedirectUri()
    {
        $result = [];

        foreach ($this->getRedirectUris() as $redirectUri) {
            $result[] = $redirectUri->getRedirectUri();
        }

        if (count($result) === 0) {
            return '';
        } elseif (count($result) === 1) {
            return $result[0];
        };

        return $result;
    }

    /**
     * @return RedirectUri[]
     */
    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function hasGrantType(string $grantType): bool
    {
        return strtolower($grantType) === $this->getGrantType();
    }

    public function isConfidential()
    {
        return true;
    }

    public function setGrantType(string $value): self
    {
        switch ($value) {
            case 'a':
                $value = self::GRANT_TYPE_AUTHORIZATION_CODE;
                break;
            case 'p':
                $value = self::GRANT_TYPE_PASSWORD;
                break;
        }

        if (!in_array($value, self::GRANT_TYPES)) {
            throw new InvalidArgumentException(sprintf("Invalid OAUTH2 grant type '%s'.", $value));
        }

        $this->grantType = $value;

        return $this;
    }

    public function setIsActive(bool $value): self
    {
        $this->isActive = $value;

        return $this;
    }

    public function setName(?string $value = null): self
    {
        $this->name = $value;

        return $this;
    }

    public function setRedirectUris(RedirectUri ...$redirectUris): self
    {
        $this->redirectUris = $redirectUris;

        return $this;
    }

    public function setSecret(string $value): self
    {
        $this->secret = $value;

        return $this;
    }

}
