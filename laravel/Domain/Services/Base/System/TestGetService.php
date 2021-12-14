<?php

namespace App\Domain\Services\Base\System;

use App\Base\Domain\Service;
use App\Domain\Entities\File;
use App\Domain\Payloads\DataPayload;
use App\Domain\Repositories\TestRepository;


class TestGetService extends Service
{

    /**
     * @var TestRepository
     */
    protected $testRepository;


    public function __construct(TestRepository $testRepository)
    {
        $this->testRepository = $testRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $e = new File($this->getUser(), 'Test', 'Test', 0);
        dd($e->getEntityMetaData()->getTableName());

        return new DataPayload();
    }
}
