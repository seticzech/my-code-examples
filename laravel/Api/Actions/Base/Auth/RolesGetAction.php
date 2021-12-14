<?php

namespace App\Api\Actions\Base\Auth;

use App\Api\Responders\Base\AuthResponder;
use App\Base\Action;
use App\Domain\Services\Base\Auth\RolesGetService;
use Illuminate\Http\Request;


class RolesGetAction extends Action
{

    public function __construct(AuthResponder $responder, RolesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $params = $request->only(['format']);

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
