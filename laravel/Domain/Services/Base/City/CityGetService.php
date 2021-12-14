<?php

namespace App\Domain\Services\Base\City;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\CityRepository;
use Exception;


class CityGetService extends Service
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
        $id = $params['id'];
        $payload = null;

        try {
            $data = $this->cityRepository->findById($id);

            if ($data) {
                $payload = new DataPayload($data);
            } else {
                $payload = new NotFoundPayload('City not found.');
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
