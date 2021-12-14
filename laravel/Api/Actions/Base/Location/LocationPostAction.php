<?php

namespace App\Api\Actions\Base\Location;

use App\Api\Responders\Base\LocationResponder;
use App\Base\Action;
use App\Domain\Services\Base\Location\LocationPostService;
use Illuminate\Http\Request;


class LocationPostAction extends Action
{

    public function __construct(LocationResponder $responder, LocationPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $post = array_only($request->post(), ['cityId', 'countryId', 'regionId']);

        $payload = $this->service->handle([], $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
