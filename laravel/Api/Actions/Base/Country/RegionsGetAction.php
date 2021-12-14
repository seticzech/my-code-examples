<?php

namespace App\Api\Actions\Base\Country;

use App\Api\Responders\Base\CountryResponder;
use App\Base\Action;
use App\Domain\Services\Base\Country\RegionsGetService;
use Illuminate\Http\Request;


class RegionsGetAction extends Action
{

    public function __construct(CountryResponder $responder, RegionsGetService $service)
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
