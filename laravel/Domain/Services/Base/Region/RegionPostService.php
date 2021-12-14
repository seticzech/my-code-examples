<?php

namespace App\Domain\Services\Base\Region;

use App\Base\Domain\Service;
use App\Domain\Payloads\CreatedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CountryRepository;
use App\Domain\Repositories\RegionRepository;
use App\Exceptions\InvalidArgumentException;
use Exception;


class RegionPostService extends Service
{

    /**
     * @var CountryRepository
     */
    protected $countryRepository;

    /**
     * @var RegionRepository
     */
    protected $regionRepository;


    public function __construct(CountryRepository $countryRepository, RegionRepository $regionRepository)
    {
        $this->countryRepository = $countryRepository;
        $this->regionRepository = $regionRepository;
    }


    /**
     * @param array $params
     * @param array $post
     * @return CreatedPayload|ExceptionPayload|ValidationPayload
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $rules = [
                'countryId' => 'int|required',
                'name' => 'string|required',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $country = $this->countryRepository->findById($post['countryId']);
            if (!$country) {
                throw new InvalidArgumentException("Country with ID {$post['countryId']} not found.");
            }

            $entity = $this->regionRepository
                ->beginTransaction()
                ->create($country, $post['name']);

            $this->regionRepository->saveAll();

            $payload = new CreatedPayload($entity);
        } catch (Exception $e) {
            $this->regionRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
