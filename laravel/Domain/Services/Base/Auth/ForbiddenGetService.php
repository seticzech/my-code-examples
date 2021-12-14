<?php

namespace App\Domain\Services\Base\Auth;

use App\Base\Domain\Service;
use App\Domain\Payloads\ForbiddenPayload;


class ForbiddenGetService extends Service
{

    public function handle(array $params = [], array $post = [])
    {
        return new ForbiddenPayload('Unauthorized');
    }

}
