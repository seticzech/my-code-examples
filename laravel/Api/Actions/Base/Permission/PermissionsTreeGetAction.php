<?php

namespace App\Api\Actions\Base\Permission;

use App\Api\Responders\Base\PermissionResponder;
use App\Base\Action;
use App\Domain\Services\Base\Permission\PermissionsTreeGetService;
use Illuminate\Http\Request;


class PermissionsTreeGetAction extends Action
{

    public function __construct(PermissionResponder $responder, PermissionsTreeGetService $service)
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
