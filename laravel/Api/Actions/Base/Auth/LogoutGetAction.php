<?php

namespace App\Api\Actions\Base\Auth;

use App\Api\Responders\Base\AuthResponder;
use App\Base\Action;
use App\Domain\Services\Base\Auth\LogoutGetService;
use Illuminate\Http\Request;


class LogoutGetAction extends Action
{

    public function __construct(AuthResponder $responder, LogoutGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
