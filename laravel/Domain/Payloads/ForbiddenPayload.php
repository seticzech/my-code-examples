<?php

namespace App\Domain\Payloads;

use App\Base\Domain\Payloads\ErrorPayload;


class ForbiddenPayload extends ErrorPayload
{

    protected $data = 'Unauthorized.';

}
