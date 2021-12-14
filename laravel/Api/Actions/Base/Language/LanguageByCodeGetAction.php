<?php

namespace App\Api\Actions\Base\Language;

use App\Api\Responders\Base\LanguageResponder;
use App\Base\Action;
use App\Domain\Services\Base\Language\LanguageGetService;
use Illuminate\Http\Request;


class LanguageByCodeGetAction extends Action
{

    public function __construct(LanguageResponder $responder, LanguageGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, string $code)
    {
        $payload = $this->service->handle(['code' => $code]);

        return $this->responder->withPayload($payload)->respond();
    }

}
