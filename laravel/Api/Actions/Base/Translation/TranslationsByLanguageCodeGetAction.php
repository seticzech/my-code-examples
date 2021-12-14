<?php

namespace App\Api\Actions\Base\Translation;

use App\Api\Responders\Base\TranslationResponder;
use App\Base\Action;
use App\Domain\Services\Base\Translation\TranslationsGetService;
use Illuminate\Http\Request;


class TranslationsByLanguageCodeGetAction extends Action
{

    public function __construct(TranslationResponder $responder, TranslationsGetService $service)
    {
        parent::__construct($responder, $service);
    }


    public function __invoke(Request $request, string $languageCode)
    {
        $params = $this->normalizeParameters($request->all(), ['format']);
        $params['languageCode'] = $languageCode;

        $payload = $this->service->handle($params);

        return $this->responder->withPayload($payload)->respond();
    }

}
