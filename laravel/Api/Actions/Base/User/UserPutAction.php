<?php

namespace App\Api\Actions\Base\User;

use App\Api\Responders\Base\UserResponder;
use App\Base\Action;
use App\Domain\Services\Base\User\UserPutService;
use Illuminate\Http\Request;


class UserPutAction extends Action
{

    public function __construct(UserResponder $responder, UserPutService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = $this->normalizeParameters($request->all(), ['format']);
        $params['id'] = $id;

        $post = array_only($request->post(), [
            'firstName',
            'lastName',
            'email',
            'username',
            'password',
            'roles',
        ]);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
