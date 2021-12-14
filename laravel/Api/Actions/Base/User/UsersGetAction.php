<?php

namespace App\Api\Actions\Base\User;

use App\Api\Responders\Base\UserResponder;
use App\Base\Action;
use App\Domain\Services\Base\User\UsersGetService;
use Illuminate\Http\Request;


class UsersGetAction extends Action
{

    public function __construct(UserResponder $responder, UsersGetService $service)
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
