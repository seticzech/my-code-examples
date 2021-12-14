<?php

namespace App\Domain\Services\Base\Region;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\RegionRepository;
use Exception;


class CitiesGetService extends Service
{

    /**
     * @var RegionRepository
     */
    protected $regionRepository;


    public function __construct(RegionRepository $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $id = $params['id'];
        $payload = null;

        try {
            $entity = $this->regionRepository->findById($id);
            if (!$entity) {
                $payload = new NotFoundPayload('Region not found.');

                return $payload;
            }

            $payload = new DataPayload($this->regionRepository->getCities($entity));
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
