<?php

namespace App\Domain\Services\Base\Country;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\CountryRepository;
use Exception;


class RegionsGetService extends Service
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
            $entity = $this->countryRepository->findById($id);
            if (!$entity) {
                $payload = new NotFoundPayload('Country not found.');

                return $payload;
            }

            $payload = new DataPayload($this->countryRepository->getRegions($entity));
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
