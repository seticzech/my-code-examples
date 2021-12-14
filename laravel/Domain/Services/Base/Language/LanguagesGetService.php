<?php

namespace App\Domain\Services\Base\Language;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\LanguageRepository;
use Exception;


class LanguagesGetService extends Service
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
        $payload = null;

        try {
            $payload = new DataPayload($this->languageRepository->findAll());
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
