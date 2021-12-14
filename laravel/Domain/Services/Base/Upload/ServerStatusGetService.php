<?php

namespace App\Domain\Services\Base\Upload;

use App\Base\Domain\Service;
use App\Base\System;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\FileRepository;
use Exception;


class ServerStatusGetService extends Service
{

    /**
     * @var FileRepository
     */
    protected $fileRepository;


    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }


    /**
     * @param array $params
     * @param array $post
     * @return DataPayload|ExceptionPayload
     */
    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $payload = new DataPayload(System::getUploadsServerStatus(true));
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
