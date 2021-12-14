<?php

namespace App\Api\Actions\Base\Upload;

use App\Api\Responders\Base\FileResponder;
use App\Base\Action;
use App\Domain\Services\Base\Upload\UploadStartPostService;
use Illuminate\Http\Request;


class UploadStartPostAction extends Action
{

    public function __construct(FileResponder $responder, UploadStartPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $post = array_only($request->post(), [
            'phase',
            'fileName',
            'fileSize',
            'mimeType',
            'userId',
        ]);

        $payload = $this->service->handle([], $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
