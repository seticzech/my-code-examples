<?php

namespace App\Api\Actions\Base\Role;

use App\Api\Responders\Base\RoleResponder;
use App\Base\Action;
use App\Domain\Services\Base\Role\PermissionsListGetService;
use Illuminate\Http\Request;


class PermissionsListGetAction extends Action
{

    public function __construct(RoleResponder $responder, PermissionsListGetService $service)
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
