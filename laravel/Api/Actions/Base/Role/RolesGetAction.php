<?php

namespace App\Api\Actions\Base\Role;

use App\Api\Responders\Base\RoleResponder;
use App\Base\Action;
use App\Domain\Services\Base\Role\RolesGetService;
use Illuminate\Http\Request;


class RolesGetAction extends Action
{

    public function __construct(RoleResponder $responder, RolesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $params = $this->normalizeParameters($request->all(), ['filter', 'format', 'format-options']);

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
