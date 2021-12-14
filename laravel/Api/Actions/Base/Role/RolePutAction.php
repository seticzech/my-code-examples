<?php

namespace App\Api\Actions\Base\Role;

use App\Api\Responders\Base\RoleResponder;
use App\Base\Action;
use App\Domain\Services\Base\Role\RolePutService;
use Illuminate\Http\Request;


class RolePutAction extends Action
{

    public function __construct(RoleResponder $responder, RolePutService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = $this->normalizeParameters($request->all(), ['format']);
        $params['id'] = $id;

        $post = array_only($request->post(), ['internalName', 'permissions', 'translations']);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
