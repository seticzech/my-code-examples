<?php

namespace App\Domain\Services\Base\Location;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\LocationRepository;
use Exception;


class LocationsGetService extends Service
{

    /**
     * @var LocationRepository
     */
    protected $locationRepository;


    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $payload = new DataPayload($this->locationRepository->findAll());
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
