<?php

namespace App\Api\Actions\Base\User;

use App\Api\Responders\Base\UserResponder;
use App\Base\Action;
use App\Domain\Services\Base\User\PermissionsListGetService;
use Illuminate\Http\Request;


class PermissionsListGetAction extends Action
{

    public function __construct(UserResponder $responder, PermissionsListGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id = null)
    {
        $params = [
            'id'=> $id
        ];

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
