<?php

namespace App\Api\Actions\Base\Region;

use App\Api\Responders\Base\RegionResponder;
use App\Base\Action;
use App\Domain\Services\Base\Region\RegionsGetService;
use Illuminate\Http\Request;


class RegionsGetAction extends Action
{

    public function __construct(RegionResponder $responder, RegionsGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
