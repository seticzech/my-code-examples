<?php

namespace App\Domain\Services\Base\User;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\UserRepository;


class UserPutService extends Service
{

    /**
     * @var UserRepository
     */
    protected $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $id = $params['id'];
        $payload = null;

        try {
            $entity = $this->userRepository->findById($id);
            if (!$entity) {
                $payload = new NotFoundPayload('User not found.');

                return $payload;
            }

            $rules = [
                'email' => ['string', 'nullable'],
                'password' => ['string', 'nullable'],
                'username' => ['string', 'nullable'],
                'firstName' => ['string', 'nullable'],
                'lastName' => ['string', 'nullable'],
                'roles' => ['array', 'nullable'],
                'roles/*' => ['int'],
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $this->userRepository->beginTransaction()
                ->update($entity, $post)
                ->saveAll();

            $payload = new DataPayload($entity);

            if (isset($params['format'])) {
                $payload->addFormatter(
                    FormatterFactory::create($params['format'])->setSource($this->userRepository)
                );
            }
        } catch (\Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
