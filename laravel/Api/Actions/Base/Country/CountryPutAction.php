<?php

namespace App\Api\Actions\Base\Country;

use App\Api\Responders\Base\CountryResponder;
use App\Base\Action;
use App\Domain\Services\Base\Country\CountryPutService;
use Illuminate\Http\Request;


class CountryPutAction extends Action
{

    public function __construct(CountryResponder $responder, CountryPutService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = ['id' => $id];
        $post = array_only($request->post(), ['name', 'isoCode2', 'isoCode3']);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
