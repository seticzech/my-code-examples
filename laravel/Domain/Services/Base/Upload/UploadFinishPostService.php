<?php

namespace App\Domain\Services\Base\Upload;

use App\Base\Domain\Service;
use App\Base\System;
use App\Base\System\File;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\FileRepository;
use App\Domain\Repositories\UserRepository;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\SystemException;
use Exception;


class UploadFinishPostService extends Service
{

    const PHASE_UPLOAD = 'upload';
    const PHASE_FINISH = 'finish';

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;


    public function __construct(FileRepository $fileRepository, UserRepository $userRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
    }


    /**
     * @param array $params
     * @param array $post
     * @return DataPayload|ExceptionPayload|ValidationPayload|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function handle(array $params = [], array $post = [])
    {
        $id = $params['id'];
        $payload = null;

        try {
            $entity = $this->fileRepository->findById($id);
            if (!$entity) {
                throw new InvalidArgumentException('File not found.');
            }
            if ($entity->getUploadedAt()) {
                throw new InvalidArgumentException('File is uploaded already.');
            }

            $this->fileRepository
                ->beginTransaction()
                ->update($entity, [
                    'uploadedAt' => new \DateTime(),
                ])
                ->saveAll();

            $payload = new DataPayload($entity);
        } catch (Exception $e) {
            $this->fileRepository->rollback();
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
