<?php

namespace App\Api\Actions\Base\Upload;

use App\Api\Responders\Base\FileResponder;
use App\Base\Action;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Services\Base\Upload\UploadPartPostService;
use Illuminate\Http\Request;


class UploadPartPostAction extends Action
{

    public function __construct(FileResponder $responder, UploadPartPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $params = ['id' => $id];

        $post = array_only($request->post(), [
            'phase',
            'attempt',
            'index',
            'offset',
            'size',
        ]);

        $post['blob'] = $request->file('blob');

        $payload = $this->service->handle($params, $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
