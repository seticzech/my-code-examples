<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name = "oauth_refresh_tokens", indexes = {
 *     @ORM\Index(name = "idx_oauth_refresh_tokens_access_token_id", columns = {"access_token_id"})
 * })
 */
class OauthRefreshToken
{

    /**
     * @ORM\Id
     * @ORM\Column(type = "string", length = 100)
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type = "string", name = "access_token_id", length = 100)
     * @var int
     */
    protected $accessTokenId;

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
