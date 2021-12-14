<?php

namespace App\Api\Actions\Base\File;

use App\Api\Responders\Base\DownloadResponder;
use App\Base\Action;
use App\Domain\Services\Base\File\DownloadGetService;
use Illuminate\Http\Request;


class DownloadGetAction extends Action
{

    public function __construct(DownloadResponder $responder, DownloadGetService $service)
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
