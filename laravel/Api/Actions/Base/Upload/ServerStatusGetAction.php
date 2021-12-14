<?php

namespace App\Api\Actions\Base\Upload;

use App\Api\Responders\Base\FileResponder;
use App\Base\Action;
use App\Domain\Services\Base\Upload\ServerStatusGetService;
use Illuminate\Http\Request;


class ServerStatusGetAction extends Action
{

    public function __construct(FileResponder $responder, ServerStatusGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
