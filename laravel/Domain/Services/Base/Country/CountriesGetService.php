<?php

namespace App\Domain\Services\Base\Country;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\CountryRepository;
use Exception;


class CountriesGetService extends Service
{

    /**
     * @var CountryRepository
     */
    protected $countryRepository;


    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $payload = new DataPayload($this->countryRepository->findAll());
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
