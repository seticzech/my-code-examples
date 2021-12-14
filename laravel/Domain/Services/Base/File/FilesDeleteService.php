<?php

namespace App\Domain\Services\Base\File;

use App\Base\Domain\Service;
use App\Base\System\File;
use App\Domain\Payloads\DeletedPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Payloads\ValidationPayload;
use App\Domain\Repositories\FileRepository;
use Exception;


class FilesDeleteService extends Service
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
        $payload = null;

        try {
            $rules = [
                'fileIds' => 'array|required',
                'fileIds/*' => 'int|required',
            ];

            $validator = $this->validate($post, $rules);
            if ($validator->fails()) {
                return new ValidationPayload($validator);
            }

            $files = $this->fileRepository->findByIds($post['fileIds']);

            $this->fileRepository
                ->deleteFiles($files);

            $payload = new DeletedPayload($files);
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;
    }

}
