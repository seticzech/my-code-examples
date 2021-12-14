<?php

namespace App\Api\Actions\Base\Navigation;

use App\Api\Responders\Base\NavigationResponder;
use App\Base\Action;
use App\Domain\Services\Base\Navigation\NavigationsGetService;
use Illuminate\Http\Request;


class NavigationsGetAction extends Action
{

    public function __construct(NavigationResponder $responder, NavigationsGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $params = $this->normalizeParameters($request->all(), ['filter', 'format', 'format-options']);

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
