<?php

namespace App\Api\Actions\Base\Country;

use App\Api\Responders\Base\CountryResponder;
use App\Base\Action;
use App\Domain\Services\Base\Country\CountriesGetService;
use Illuminate\Http\Request;


class CountriesGetAction extends Action
{

    public function __construct(CountryResponder $responder, CountriesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
