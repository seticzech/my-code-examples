<?php

namespace App\Domain\Services\Base\User;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\CreatedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\UserRepository;


class UserPostService extends Service
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
        $payload = null;

        try {
            $rules = [
                'email' => 'string',
                'password' => 'string',
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

            $user = $this->userRepository->create($post['email']);

            $this->userRepository->update($user, $post)
                ->save($user);

            $payload = new CreatedPayload($user);

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
