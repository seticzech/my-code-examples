<?php

namespace App\Api\Actions\Base\Location;

use App\Api\Responders\Base\LocationResponder;
use App\Base\Action;
use App\Domain\Services\Base\Location\LocationPutService;
use Illuminate\Http\Request;


class LocationPutAction extends Action
{

    public function __construct(LocationResponder $responder, LocationPutService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = ['id' => $id];
        $post = array_only($request->post(), ['cityId', 'countryId', 'regionId']);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
