<?php

namespace App\Domain\Services\Base\Role;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\RoleRepository;
use Exception;


class RolePutService extends Service
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
            $entity = $this->roleRepository->findById($id);
            if (!$entity) {
                $payload = new NotFoundPayload('Role not found.');

                return $payload;
            }

            $rules = [
                'internalName' => ['string', 'nullable'],
                'permissions' => ['array', 'nullable'],
                'translations' => ['array', 'nullable'],
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $this->roleRepository->beginTransaction()
                ->update($entity, $post)
                ->saveAll();

            $payload = new DataPayload($entity);

            if (isset($params['format'])) {
                $payload->addFormatter(
                    FormatterFactory::create($params['format'])->setSource($this->roleRepository)
                );
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
