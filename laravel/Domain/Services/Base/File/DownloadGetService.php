<?php

namespace App\Domain\Services\Base\File;

use App\Base\Domain\Service;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\DownloadPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\NotFoundPayload;
use App\Domain\Repositories\FileRepository;
use Exception;


class DownloadGetService extends Service
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
            $data = $this->fileRepository->findById($id);

            if ($data) {
                $payload = new DownloadPayload($data);
            } else {
                $payload = new NotFoundPayload('File not found.');
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
