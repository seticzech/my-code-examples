<?php

namespace App\Entity\Erp\OAuth2;

use App\Entity\EntityAbstract;
use App\Traits\Entity\OAuth2\RevocableTrait;
use App\Traits\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name = "bb_erp.oauth_authorization_codes")
 */
class AuthorizationCode extends EntityAbstract
{

    use RevocableTrait, TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type = "string", length = 80, unique = true)
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type = "string", name = "user_id", length = 128, nullable = true)
     *
     * @var string
     */
    protected $userId;

    /**
     * @ORM\ManyToOne(targetEntity = "Client")
     * @ORM\JoinColumn(
     *     nullable = false,
     *     onDelete = "CASCADE"
     * )
     *
     * @var Client
     */
    protected $client;

}
