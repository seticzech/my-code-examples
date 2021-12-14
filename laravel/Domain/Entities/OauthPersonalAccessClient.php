<?php

namespace App\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name = "oauth_personal_access_clients", indexes = {
 *     @ORM\Index(name = "idx_oauth_personal_access_clients_client_id", columns = {"client_id"})
 * })
 */
class OauthPersonalAccessClient
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "IDENTITY")
     * @ORM\Column(type = "integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type = "integer", name = "client_id")
     * @var int
     */
    protected $clientId;

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
