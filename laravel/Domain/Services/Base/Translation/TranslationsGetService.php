<?php

namespace App\Domain\Services\Base\Translation;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\TranslationRepository;
use Exception;


class TranslationsGetService extends Service
{

    /**
     * @var TranslationRepository
     */
    protected $translationRepository;


    public function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            if (isset($params['languageCode'])) {
                $payload = new DataPayload($this->translationRepository->findByLanguageCode($params['languageCode']));

                if (isset($params['format'])) {
                    $payload->addFormatter(
                        FormatterFactory::create($params['format'])->setSource($this->translationRepository)
                    );
                }
            } else {
                $payload = new DataPayload($this->translationRepository->findAll());
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
