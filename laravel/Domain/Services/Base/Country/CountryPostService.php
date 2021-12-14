<?php

namespace App\Domain\Services\Base\Country;

use App\Base\Domain\Service;
use App\Domain\Payloads\CreatedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CountryRepository;
use Exception;


class CountryPostService extends Service
{

    /**
     * @var CountryRepository
     */
    protected $countryRepository;


    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }


    /**
     * @param array $params
     * @param array $post
     * @return CreatedPayload|ExceptionPayload|ValidationPayload|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $rules = [
                'name' => 'string|required',
                'isoCode2' => 'string|required|size:2',
                'isoCode3' => 'string|required|size:3',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $entity = $this->countryRepository
                ->beginTransaction()
                ->create($post['name'], $post['isoCode2'], $post['isoCode3']);

            $this->countryRepository->saveAll();

            $payload = new CreatedPayload($entity);
        } catch (Exception $e) {
            $this->countryRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
