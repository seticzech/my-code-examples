<?php

namespace App\Api\Actions\Base\Location;

use App\Api\Responders\Base\LocationResponder;
use App\Base\Action;
use App\Domain\Services\Base\Location\AvailableCitiesGetService;
use Illuminate\Http\Request;


class AvailableCitiesGetAction extends Action
{

    public function __construct(LocationResponder $responder, AvailableCitiesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $regionId)
    {
        $params = ['regionId' => $regionId];

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
