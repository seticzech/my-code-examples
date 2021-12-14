<?php

namespace App\Domain\Services\Base\Region;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\RegionRepository;
use Exception;


class RegionsGetService extends Service
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
        $payload = null;

        try {
            $payload = new DataPayload($this->regionRepository->findAll());
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
