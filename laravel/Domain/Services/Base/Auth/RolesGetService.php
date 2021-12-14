<?php

namespace App\Domain\Services\Base\Auth;

use App\Base\Domain\Service;
use App\Domain\Entities\User;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Auth;


class RolesGetService extends Service
{

    /**
     * @var UserRepository
     */
    protected $userRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * @param array $params
     * @param array $post
     * @return DataPayload
     * @throws \App\Exceptions\InvalidArgumentException
     */
    public function handle(array $params = [], array $post = [])
    {
        /** @var User $user */
        $user = Auth::user();

        $payload = null;

        try {
            if ($user) {
                $payload = new DataPayload($this->userRepository->getRoles($user));

                if (isset($params['format'])) {
                    $payload->addFormatter(
                        FormatterFactory::create($params['format'])->setSource('Role')
                    );
                }
            } else {
                $payload = new DataPayload();
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
