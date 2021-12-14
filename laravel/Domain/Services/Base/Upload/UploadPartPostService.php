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
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\SystemException;
use Exception;
use Illuminate\Http\UploadedFile;


class UploadPartPostService extends Service
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
        $id = $params['id'];
        $payload = null;

        try {
            $rules = [
                'blob' => 'required',
                'offset' => 'integer|required',
                'size' => 'integer|required',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $entity = $this->fileRepository->findById($id);
            if (!$entity) {
                throw new InvalidArgumentException('File not found.');
            }
            if ($entity->getUploadedAt()) {
                throw new InvalidArgumentException('File is uploaded already.');
            }

            /** @var UploadedFile $blob */
            $blob = $post['blob'];

            $source = new File($blob->path());
            $binary = $source->read();
            $source->close();

            $file = new File($entity->getRealName());
            $file->writeAtOffset($post['offset'], $binary)
                ->close();

            //sleep(1);

            $payload = new DataPayload($entity);
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
