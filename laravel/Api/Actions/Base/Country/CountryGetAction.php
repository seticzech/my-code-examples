<?php

namespace App\Api\Actions\Base\Country;

use App\Api\Responders\Base\CountryResponder;
use App\Base\Action;
use App\Domain\Services\Base\Country\CountryGetService;
use Illuminate\Http\Request;


class CountryGetAction extends Action
{

    public function __construct(CountryResponder $responder, CountryGetService $service)
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
