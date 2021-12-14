<?php

namespace App\Api\Actions\Base\Upload;

use App\Api\Responders\Base\FileResponder;
use App\Base\Action;
use App\Domain\Services\Base\Upload\UploadFinishPostService;
use Illuminate\Http\Request;


class UploadFinishPostAction extends Action
{

    public function __construct(FileResponder $responder, UploadFinishPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = ['id' => $id];

        $post = array_only($request->post(), [
            'phase',
        ]);

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
