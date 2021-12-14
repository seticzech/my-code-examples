<?php

namespace App\Domain\Services\Base\Auth;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use Illuminate\Support\Facades\Auth;


class MeGetService extends Service
{

    public function handle(array $params = [], array $post = [])
    {
        return new DataPayload(Auth::user());
    }

}
