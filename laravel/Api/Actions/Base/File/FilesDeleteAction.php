<?php

namespace App\Api\Actions\Base\File;

use App\Api\Responders\Base\FileResponder;
use App\Base\Action;
use App\Domain\Services\Base\File\FilesDeleteService;
use Illuminate\Http\Request;


class FilesDeleteAction extends Action
{

    public function __construct(FileResponder $responder, FilesDeleteService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $post = array_only($request->post(), [
            'fileIds',
        ]);

        $payload = $this->service->handle([], $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
