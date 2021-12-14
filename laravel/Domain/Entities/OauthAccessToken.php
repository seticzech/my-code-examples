<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name = "oauth_access_tokens", indexes = {
 *     @ORM\Index(name = "idx_oauth_access_tokens_user_id", columns = {"user_id"})
 * })
 */
class OauthAccessToken
{

    /**
     * @ORM\Id
     * @ORM\Column(type = "string", length = 100)
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type = "integer", name = "user_id", nullable = true)
     * @var int
     */
    protected $userId;

    /**
     * @ORM\Column(type = "integer", name = "client_id")
     * @var int
     */
    protected $clientId;

    /**
     * @ORM\Column(type = "string", nullable = true)
     * @var string
     */
    protected $name;

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
     * @ORM\Column(type = "datetime", name = "created_at", nullable = true)
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type = "datetime", name = "updated_at", nullable = true)
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type = "datetime", name = "expires_at", nullable = true)
     * @var DateTime
     */
    protected $expiresAt;

}
