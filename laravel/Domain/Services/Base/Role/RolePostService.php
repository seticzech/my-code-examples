<?php

namespace App\Domain\Services\Base\Role;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\CreatedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\RoleRepository;
use Exception;


class RolePostService extends Service
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
        $payload = null;

        try {
            $rules = [
                'internalName' => ['string', 'nullable'],
                'translations' => ['array', 'required'],
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            if (!isset($post['internalName'])) {
                $post['internalName'] = $this->roleRepository->findTranslation($post['translations']);
            }

            $entity = $this->roleRepository->create($post['internalName']);
            $this->roleRepository->setTranslations($entity, $post['translations'])
                ->save($entity);

            $payload = new CreatedPayload($entity);

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
