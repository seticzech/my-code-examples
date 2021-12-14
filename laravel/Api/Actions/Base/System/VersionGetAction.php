<?php

namespace App\Api\Actions\Base\System;

use App\Api\Responders\Base\SystemResponder;
use App\Base\Action;
use App\Domain\Services\Base\System\VersionGetService;
use Illuminate\Http\Request;


class VersionGetAction extends Action
{

    public function __construct(SystemResponder $responder, VersionGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle($request->toArray());

        return $this->responder->withPayload($payload)->respond();
    }

}
