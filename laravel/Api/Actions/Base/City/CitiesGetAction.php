<?php

namespace App\Api\Actions\Base\City;

use App\Api\Responders\Base\CityResponder;
use App\Base\Action;
use App\Domain\Services\Base\City\CitiesGetService;
use Illuminate\Http\Request;


class CitiesGetAction extends Action
{

    public function __construct(CityResponder $responder, CitiesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
