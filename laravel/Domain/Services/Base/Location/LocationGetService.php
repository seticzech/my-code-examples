<?php

namespace App\Domain\Services\Base\Location;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\LocationRepository;
use Exception;


class LocationGetService extends Service
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
        $id = $params['id'];
        $payload = null;

        try {
            $data = $this->locationRepository->findById($id);

            if ($data) {
                $payload = new DataPayload($data);
            } else {
                $payload = new NotFoundPayload('Location not found.');
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
