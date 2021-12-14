<?php

namespace App\Service\Erp\Sso\Contract;

use App\Entity\Erp\User;

interface SsoServiceInterface
{

    public function processPayload($payload): User;

}
