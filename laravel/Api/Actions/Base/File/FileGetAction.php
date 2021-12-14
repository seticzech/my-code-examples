<?php

namespace App\Api\Actions\Base\File;

use App\Api\Responders\Base\FileResponder;
use App\Base\Action;
use App\Domain\Services\Base\File\FileGetService;
use Illuminate\Http\Request;


class FileGetAction extends Action
{

    public function __construct(FileResponder $responder, FileGetService $service)
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
