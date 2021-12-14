<?php

namespace App\Api\Actions\Base\Country;

use App\Api\Responders\Base\CountryResponder;
use App\Base\Action;
use App\Domain\Services\Base\Country\CountryPostService;
use Illuminate\Http\Request;


class CountryPostAction extends Action
{

    public function __construct(CountryResponder $responder, CountryPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $post = array_only($request->post(), ['name', 'isoCode2', 'isoCode3']);

        $payload = $this->service->handle([], $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
