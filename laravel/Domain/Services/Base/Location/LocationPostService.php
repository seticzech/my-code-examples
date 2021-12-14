<?php

namespace App\Domain\Services\Base\Location;

use App\Base\Domain\Service;
use App\Domain\Payloads\CreatedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CityRepository;
use App\Domain\Repositories\CountryRepository;
use App\Domain\Repositories\LocationRepository;
use App\Domain\Repositories\RegionRepository;
use App\Exceptions\InvalidArgumentException;
use Exception;


class LocationPostService extends Service
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
     * @return CreatedPayload|ExceptionPayload|ValidationPayload|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
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

            $entity = $this->locationRepository
                ->beginTransaction()
                ->create($country, $region, $city);

            $this->locationRepository->saveAll();

            $payload = new CreatedPayload($entity);
        } catch (Exception $e) {
            $this->locationRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
