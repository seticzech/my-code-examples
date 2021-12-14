<?php

namespace App\Domain\Services\Base\City;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\CityRepository;
use Exception;


class CitiesGetService extends Service
{

    /**
     * @var CityRepository
     */
    protected $cityRepository;


    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $payload = new DataPayload($this->cityRepository->findAll());
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
