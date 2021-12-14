<?php

namespace App\Api\Actions\Base\Language;

use App\Api\Responders\Base\LanguageResponder;
use App\Base\Action;
use App\Domain\Services\Base\Language\LanguagesReorderPostService;
use Illuminate\Http\Request;


class LanguagesReorderPostAction extends Action
{

    public function __construct(LanguageResponder $responder, LanguagesReorderPostService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $post = array_only($request->post(), ['idList']);

        $payload = $this->service->handle([], $post);

        return $this->responder->withPayload($payload)->respond();
    }

}
