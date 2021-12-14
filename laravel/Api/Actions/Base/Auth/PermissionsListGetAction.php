<?php

namespace App\Api\Actions\Base\Auth;

use App\Api\Responders\Base\AuthResponder;
use App\Base\Action;
use App\Domain\Services\Base\Auth\PermissionsListGetService;
use Illuminate\Http\Request;


class PermissionsListGetAction extends Action
{

    public function __construct(AuthResponder $responder, PermissionsListGetService $service)
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
