<?php

namespace App\Domain\Services\Base\Language;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\LanguageRepository;
use Exception;


class LanguageGetService extends Service
{

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;


    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $result = null;
        $payload = null;

        try {
            if (isset($params['id'])) {
                $result = $this->languageRepository->findById($params['id']);
            } elseif (isset($params['code'])) {
                $result = $this->languageRepository->findByCode($params['code']);
            }

            $payload = $result
                ? new DataPayload($result)
                : new NotFoundPayload('Language not found.');
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
