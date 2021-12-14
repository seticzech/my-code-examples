<?php

namespace App\Api\Actions\Base\City;

use App\Api\Responders\Base\CityResponder;
use App\Base\Action;
use App\Domain\Services\Base\City\CityPostService;
use Illuminate\Http\Request;


class CityPostAction extends Action
{

    public function __construct(CityResponder $responder, CityPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $post = array_only($request->post(), ['name', 'regionId']);

        $payload = $this->service->handle([], $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
