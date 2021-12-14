<?php

namespace App\Api\Actions\Base\Language;

use App\Api\Responders\Base\LanguageResponder;
use App\Base\Action;
use App\Domain\Services\Base\Language\LanguageGetService;
use Illuminate\Http\Request;


class LanguageGetAction extends Action
{

    public function __construct(LanguageResponder $responder, LanguageGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, int $id)
    {
        $payload = $this->service->handle(['id' => $id]);

        return $this->responder->withPayload($payload)->respond();
    }

}
