<?php

namespace App\Domain\Services\Base\Location;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\CountryRepository;
use App\Domain\Repositories\LocationRepository;
use App\Domain\Repositories\RegionRepository;
use App\Exceptions\InsufficientDataException;
use Exception;


class AvailableCitiesGetService extends Service
{

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
        CountryRepository $countryRepository,
        LocationRepository $locationRepository,
        RegionRepository $regionRepository)
    {
        $this->countryRepository = $countryRepository;
        $this->locationRepository = $locationRepository;
        $this->regionRepository = $regionRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $regionId = $params['regionId'];
        $payload = null;

        try {
            $region = $this->regionRepository->findById($regionId);
            if (!$region) {
                $payload  = new NotFoundPayload('Region not found.');

                return $payload;
            }

            $payload = new DataPayload($this->locationRepository->findAvailableCities($region));
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
