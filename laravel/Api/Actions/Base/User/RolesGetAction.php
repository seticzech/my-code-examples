<?php

namespace App\Api\Actions\Base\User;

use App\Api\Responders\Base\UserResponder;
use App\Base\Action;
use App\Domain\Services\Base\User\RolesGetService;
use Illuminate\Http\Request;


class RolesGetAction extends Action
{

    public function __construct(UserResponder $responder, RolesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id = null)
    {
        $params = $this->normalizeParameters($request->all(), ['format']);
        $params['id'] = $id;

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
