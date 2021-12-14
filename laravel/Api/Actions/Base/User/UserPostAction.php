<?php

namespace App\Api\Actions\Base\User;

use App\Api\Responders\Base\UserResponder;
use App\Base\Action;
use App\Domain\Services\Base\User\UserPostService;
use Illuminate\Http\Request;


class UserPostAction extends Action
{

    public function __construct(UserResponder $responder, UserPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $params = $this->normalizeParameters($request->all(), ['format']);

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
