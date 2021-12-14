<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\Timestampable;


/**
 * @ORM\Entity
 * @ORM\Table(name = "oauth_clients", indexes = {
 *     @ORM\Index(name = "idx_oauth_clients_user_id", columns = {"user_id"})
 * })
 */
class OauthClients
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "IDENTITY")
     * @ORM\Column(type = "integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type = "integer", name = "user_id", nullable = true)
     * @var int
     */
    protected $userId;

    /**
     * @ORM\Column(type = "string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", length = 100)
     * @var string
     */
    protected $secret;

    /**
     * @ORM\Column(type = "text")
     * @var string
     */
    protected $redirect;

    /**
     * @ORM\Column(type = "boolean", name = "personal_access_client")
     * @var boolean
     */
    protected $personalAccessClient;

    /**
     * @ORM\Column(type = "boolean", name = "password_client")
     * @var boolean
     */
    protected $passwordClient;

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

}
