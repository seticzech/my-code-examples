<?php

namespace App\Domain\Services\Base\Language;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\LanguageRepository;
use Exception;


class LanguagesReorderPostService extends Service
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
            $rules = [
                'idList' => ['array', 'required'],
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $this->languageRepository
                ->beginTransaction()
                ->reorder($post['idList'])
                ->saveAll();

            $payload = new DataPayload($this->languageRepository->findAll());
        } catch (Exception $e) {
            $this->languageRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
