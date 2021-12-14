<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name = "oauth_auth_codes")
 */
class OauthAuthCode
{

    /**
     * @ORM\Id
     * @ORM\Column(type = "string", length = 100)
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type = "integer", name = "user_id")
     * @var int
     */
    protected $userId;

    /**
     * @ORM\Column(type = "integer", name = "client_id")
     * @var int
     */
    protected $clientId;

    /**
     * @ORM\Column(type = "text", nullable = true)
     * @var string
     */
    protected $scopes;

    /**
     * @ORM\Column(type = "boolean")
     * @var boolean
     */
    protected $revoked;

    /**
     * @ORM\Column(type = "datetime", name = "expires_at", nullable = true)
     * @var DateTime
     */
    protected $expiresAt;

}
