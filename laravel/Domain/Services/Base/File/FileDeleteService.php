<?php

namespace App\Domain\Services\Base\File;

use App\Base\Domain\Service;
use App\Base\System\File;
use App\Domain\Payloads\DeletedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\FileRepository;
use Exception;


class FileDeleteService extends Service
{

    /**
     * @var FileRepository
     */
    protected $fileRepository;


    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $id = $params['id'];
        $payload = null;

        try {
            $entity = $this->fileRepository->findById($id);
            if (!$entity) {
                return new NotFoundPayload('File not found.');
            }

            $this->fileRepository
                ->delete($entity);

            $payload = new DeletedPayload($entity);
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
