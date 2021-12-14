<?php

namespace App\Api\Actions\Base\Region;

use App\Api\Responders\Base\RegionResponder;
use App\Base\Action;
use App\Domain\Services\Base\Region\RegionPostService;
use Illuminate\Http\Request;


class RegionPostAction extends Action
{

    public function __construct(RegionResponder $responder, RegionPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $post = array_only($request->post(), ['countryId', 'name']);

        $payload = $this->service->handle([], $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
