<?php

namespace App\Api\Actions\Base\Auth;

use App\Api\Responders\Base\AuthResponder;
use App\Base\Action;
use App\Domain\Services\Base\Auth\ForbiddenGetService;
use Illuminate\Http\Request;


class ForbiddenGetAction extends Action
{

    public function __construct(AuthResponder $responder, ForbiddenGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
