<?php

namespace App\Api\Actions\Base\Language;

use App\Api\Responders\Base\LanguageResponder;
use App\Base\Action;
use App\Domain\Services\Base\Language\LanguagesGetService;
use Illuminate\Http\Request;


class LanguagesGetAction extends Action
{

    public function __construct(LanguageResponder $responder, LanguagesGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request)
    {
        $payload = $this->service->handle();

        return $this->responder->withPayload($payload)->respond();
    }

}
