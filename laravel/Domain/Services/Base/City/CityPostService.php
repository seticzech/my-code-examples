<?php

namespace App\Domain\Services\Base\City;

use App\Base\Domain\Service;
use App\Domain\Payloads\CreatedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CityRepository;
use App\Domain\Repositories\RegionRepository;
use App\Exceptions\InvalidArgumentException;
use Exception;


class CityPostService extends Service
{

    /**
     * @var CityRepository
     */
    protected $cityRepository;

    /**
     * @var RegionRepository
     */
    protected $regionRepository;


    public function __construct(CityRepository $cityRepository, RegionRepository $regionRepository)
    {
        $this->cityRepository = $cityRepository;
        $this->regionRepository = $regionRepository;
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
                'regionId' => 'int|required',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $region = $this->regionRepository->findById($post['regionId']);
            if (!$region) {
                throw new InvalidArgumentException("Region with ID {$post['regionId']} not found.");
            }

            $entity = $this->cityRepository
                ->beginTransaction()
                ->create($region, $post['name']);

            $this->cityRepository->saveAll();

            $payload = new CreatedPayload($entity);
        } catch (Exception $e) {
            $this->cityRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
