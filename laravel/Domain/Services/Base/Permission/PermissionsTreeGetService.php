<?php

namespace App\Domain\Services\Base\Permission;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\PermissionRepository;
use Exception;


class PermissionsTreeGetService extends Service
{

    /**
     * @var PermissionRepository
     */
    protected $permissionRepository;


    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $payload = new DataPayload($this->permissionRepository->getTree());

            if (isset($params['format'])) {
                $payload->addFormatter(
                    FormatterFactory::create($params['format'])->setSource('PermissionTree')
                );
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
