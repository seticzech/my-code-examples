<?php

namespace App\Domain\Services\Base\System;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;


class VersionGetService extends Service
{

    public function handle(array $params = [], array $post = [])
    {
        return new DataPayload([
            'version' => '1.0.0',
        ]);
    }
}
