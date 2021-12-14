<?php

namespace App\Domain\Services\Base\Country;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\CountryRepository;
use Exception;


class CountryGetService extends Service
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
        $id = $params['id'];
        $payload = null;

        try {
            $data = $this->countryRepository->findById($id);

            if ($data) {
                $payload = new DataPayload($data);
            } else {
                $payload = new NotFoundPayload('Country not found.');
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
