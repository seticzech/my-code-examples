<?php

namespace App\Api\Actions\Base\Location;

use App\Api\Responders\Base\LocationResponder;
use App\Base\Action;
use App\Domain\Services\Base\Location\LocationsGetService;
use Illuminate\Http\Request;


class LocationsGetAction extends Action
{

    public function __construct(LocationResponder $responder, LocationsGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
