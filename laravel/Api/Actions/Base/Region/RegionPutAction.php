<?php

namespace App\Api\Actions\Base\Region;

use App\Api\Responders\Base\RegionResponder;
use App\Base\Action;
use App\Domain\Services\Base\Region\RegionPutService;
use Illuminate\Http\Request;


class RegionPutAction extends Action
{

    public function __construct(RegionResponder $responder, RegionPutService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = ['id' => $id];
        $post = array_only($request->post(), ['countryId', 'name']);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
