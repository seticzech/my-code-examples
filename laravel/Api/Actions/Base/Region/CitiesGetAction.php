<?php

namespace App\Api\Actions\Base\Region;

use App\Api\Responders\Base\RegionResponder;
use App\Base\Action;
use App\Domain\Services\Base\Region\CitiesGetService;
use Illuminate\Http\Request;


class CitiesGetAction extends Action
{

    public function __construct(RegionResponder $responder, CitiesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = ['id' => $id];

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
