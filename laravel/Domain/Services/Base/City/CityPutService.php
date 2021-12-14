<?php

namespace App\Domain\Services\Base\City;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CountryRepository;
use App\Domain\Repositories\CityRepository;
use App\Domain\Repositories\RegionRepository;
use App\Exceptions\InvalidArgumentException;
use Exception;


class CityPutService extends Service
{

    /**
     * @var CountryRepository
     */
    protected $countryRepository;

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
     * @return DataPayload|ExceptionPayload|NotFoundPayload|ValidationPayload|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function handle(array $params = [], array $post = [])
    {
        $id = $params['id'];
        $payload = null;

        try {
            $entity = $this->cityRepository->findById($id);
            if (!$entity) {
                $payload = new NotFoundPayload('City not found.');

                return $payload;
            }

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

            $data = [
                'name' => $post['name'],
                'region' => $region,
            ];

            $this->cityRepository
                ->beginTransaction()
                ->update($entity, $data)
                ->saveAll();

            $payload = new DataPayload($entity);
        } catch (Exception $e) {
            $this->cityRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
