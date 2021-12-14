<?php

namespace App\Domain\Services\Base\Region;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\CountryRepository;
use App\Domain\Repositories\RegionRepository;
use App\Exceptions\InvalidArgumentException;
use Exception;


class RegionPutService extends Service
{

    /**
     * @var CountryRepository
     */
    protected $countryRepository;

    /**
     * @var RegionRepository
     */
    protected $regionRepository;


    public function __construct(CountryRepository $countryRepository, RegionRepository $regionRepository)
    {
        $this->countryRepository = $countryRepository;
        $this->regionRepository = $regionRepository;
    }


    /**
     * @param array $params
     * @param array $post
     * @return DataPayload|ExceptionPayload|NotFoundPayload|ValidationPayload|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
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

            $rules = [
                'countryId' => 'int|required',
                'name' => 'string|required',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $country = $this->countryRepository->findById($post['countryId']);
            if (!$country) {
                throw new InvalidArgumentException("Country with ID {$post['countryId']} not found.");
            }

            $data = [
                'country' => $country,
                'name' => $post['name'],
            ];

            $this->regionRepository
                ->beginTransaction()
                ->update($entity, $data)
                ->saveAll();

            $payload = new DataPayload($entity);
        } catch (Exception $e) {
            $this->regionRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
