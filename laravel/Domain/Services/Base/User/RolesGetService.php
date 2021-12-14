<?php

namespace App\Domain\Services\Base\User;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\UserRepository;
use Exception;


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


    public function handle(array $params = [], array $post = [])
    {
        $id = $params['id'];
        $payload = null;

        try {
            $data = $this->userRepository->findById($id);

            if ($data) {
                $payload = new DataPayload($this->userRepository->getRoles($data));

                if (isset($params['format'])) {
                    $payload->addFormatter(
                        FormatterFactory::create($params['format'])->setSource('Roles')
                    );
                }
            } else {
                $payload = new NotFoundPayload('User not found.');
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
