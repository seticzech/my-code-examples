<?php

namespace App\Domain\Services\Base\Role;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\RoleRepository;
use Exception;


class PermissionsGetService extends Service
{

    /**
     * @var RoleRepository
     */
    protected $roleRepository;


    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $id = $params['id'];
        $payload = null;

        try {
            $data = $this->roleRepository->findById($id);

            if ($data) {
                $payload = new DataPayload($this->roleRepository->getPermissions($data));

                if (isset($params['format'])) {
                    $payload->addFormatter(
                        FormatterFactory::create($params['format'])->setSource('RolePermissions')
                    );
                }
            } else {
                $payload = new NotFoundPayload('Role not found.');
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
