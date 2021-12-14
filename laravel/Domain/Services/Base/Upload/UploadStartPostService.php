<?php

namespace App\Domain\Services\Base\Upload;

use App\Base\Domain\Service;
use App\Base\System;
use App\Base\System\File;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\FileRepository;
use App\Domain\Repositories\UserRepository;
use App\Exceptions\SystemException;
use Exception;


class UploadStartPostService extends Service
{

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
     * @return DataPayload|ExceptionPayload|NotFoundPayload|ValidationPayload
     * @throws SystemException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function handle(array $params = [], array $post = [])
    {
        $file = null;
        $payload = null;

        try {
            $rules = [
                'fileName' => 'string|required',
                'fileSize' => 'int|required',
                'mimeType' => 'string|required',
                'userId' => 'int|required',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $user = $this->userRepository->findById($post['userId']);
            if (!$user) {
                return new NotFoundPayload('User not found.');
            }

            $entity = $this->fileRepository
                ->beginTransaction()
                ->create($user, $post['fileName'], $post['mimeType'], $post['fileSize']);

            $updateData = array_merge([
                'partialUpload' => true,
                'path' => System::getAppDataPath(),
            ]);
            $this->fileRepository->update($entity, $updateData);

            if (!System::hasFreeSpaceForUpload($entity->getRealName(), $entity->getSize())) {
                throw new SystemException('There is not enough free space on drive for upload file.');
            }

            $file = File::create($entity->getRealName(), false, $entity->getSize());

            $this->fileRepository->saveAll();

            //sleep(1);

            $payload = new DataPayload($entity);
        } catch (Exception $e) {
            if ($file) {
                $file->remove();
            }
            $this->fileRepository->rollback();

            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
