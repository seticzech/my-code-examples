<?php

namespace App\Api\Actions\Base\Location;

use App\Api\Responders\Base\LocationResponder;
use App\Base\Action;
use App\Domain\Services\Base\Location\LocationGetService;
use Illuminate\Http\Request;


class LocationGetAction extends Action
{

    public function __construct(LocationResponder $responder, LocationGetService $service)
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
