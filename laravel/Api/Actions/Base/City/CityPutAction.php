<?php

namespace App\Api\Actions\Base\City;

use App\Api\Responders\Base\CityResponder;
use App\Base\Action;
use App\Domain\Services\Base\City\CityPutService;
use Illuminate\Http\Request;


class CityPutAction extends Action
{

    public function __construct(CityResponder $responder, CityPutService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = ['id' => $id];
        $post = array_only($request->post(), ['name', 'regionId']);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
