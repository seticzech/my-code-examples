<?php

namespace App\Domain\Services\Base\Country;

use App\Base\Domain\Service;
use App\Domain\Payloads\CreatedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CountryRepository;
use App\Exceptions\InvalidArgumentException;
use Exception;


class CountryPutService extends Service
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
        $id = $params['id'];
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

            $entity = $this->countryRepository->findById($id);
            if (!$entity) {
                throw new InvalidArgumentException('Country not found.');
            }

            $this->countryRepository
                ->beginTransaction()
                ->update($entity, $post)
                ->saveAll();

            $payload = new CreatedPayload($entity);
        } catch (Exception $e) {
            $this->countryRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
