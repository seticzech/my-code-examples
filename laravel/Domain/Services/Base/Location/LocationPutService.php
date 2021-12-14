<?php

namespace App\Domain\Services\Base\Location;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CityRepository;
use App\Domain\Repositories\CountryRepository;
use App\Domain\Repositories\LocationRepository;
use App\Domain\Repositories\RegionRepository;
use App\Exceptions\InvalidArgumentException;
use Exception;


class LocationPutService extends Service
{

    /**
     * @var CityRepository
     */
    protected $cityRepository;

    /**
     * @var CountryRepository
     */
    protected $countryRepository;

    /**
     * @var LocationRepository
     */
    protected $locationRepository;

    /**
     * @var RegionRepository
     */
    protected $regionRepository;


    public function __construct(
        CityRepository $cityRepository,
        CountryRepository $countryRepository,
        LocationRepository $locationRepository,
        RegionRepository $regionRepository)
    {
        $this->cityRepository = $cityRepository;
        $this->countryRepository = $countryRepository;
        $this->locationRepository = $locationRepository;
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
            $entity = $this->locationRepository->findById($id);
            if (!$entity) {
                $payload = new NotFoundPayload('Location not found.');

                return $payload;
            }

            $rules = [
                'cityId' => 'int|required',
                'countryId' => 'int|required',
                'regionId' => 'int|required',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $country = $this->countryRepository->findById($post['countryId']);
            if (!$country) {
                throw new InvalidArgumentException("Country with ID {$post['countryId']} not found.");
            }

            $region = $this->regionRepository->findById($post['regionId']);
            if (!$region) {
                throw new InvalidArgumentException("Region with ID {$post['regionId']} not found.");
            }

            $city = $this->cityRepository->findById($post['cityId']);
            if (!$city) {
                throw new InvalidArgumentException("City with ID {$post['cityId']} not found.");
            }

            if (!$this->countryRepository->hasRegion($country, $region)) {
                throw new InvalidArgumentException('Specified country does not contains specified region.');
            }
            if (!$this->regionRepository->hasCity($region, $city)) {
                throw new InvalidArgumentException('Specified region does not contains specified city.');
            }

            $data = [
                'city' => $city,
                'country' => $country,
                'region' => $region,
            ];

            $this->locationRepository
                ->beginTransaction()
                ->update($entity, $data)
                ->saveAll();

            $payload = new DataPayload($entity);
        } catch (Exception $e) {
            $this->locationRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
