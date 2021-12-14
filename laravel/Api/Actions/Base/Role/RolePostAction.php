<?php

namespace App\Api\Actions\Base\Role;

use App\Api\Responders\Base\RoleResponder;
use App\Base\Action;
use App\Domain\Services\Base\Role\RolePostService;
use Illuminate\Http\Request;


class RolePostAction extends Action
{

    public function __construct(RoleResponder $responder, RolePostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $params = $this->normalizeParameters($request->all(), ['format']);

        $post = array_only($request->post(), ['internalName', 'translations']);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
