<?php

namespace App\Api\Actions\Base\City;

use App\Api\Responders\Base\CityResponder;
use App\Base\Action;
use App\Domain\Services\Base\City\CityGetService;
use Illuminate\Http\Request;


class CityGetAction extends Action
{

    public function __construct(CityResponder $responder, CityGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id = null)
    {
        $params = [
            'id' => $id,
        ];

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
