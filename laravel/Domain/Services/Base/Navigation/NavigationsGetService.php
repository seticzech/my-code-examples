<?php

namespace App\Domain\Services\Base\Navigation;

use App\Base\Domain\Service;
use App\Domain\Formatters\FormatterFactory;
use App\Domain\Payloads\DataPayload;
use App\Domain\Payloads\ExceptionPayload;
use App\Domain\Repositories\NavigationRepository;
use Exception;


class NavigationsGetService extends Service
{

    /**
     * @var NavigationRepository
     */
    protected $navigationRepository;


    public function __construct(NavigationRepository $navigationRepository)
    {
        $this->navigationRepository = $navigationRepository;
    }


    public function handle(array $params = [], array $post = [])
    {
        $payload = null;

        try {
            $payload = new DataPayload($this->navigationRepository->findAllParent());

            if (isset($params['format'])) {
                $payload->addFormatter(
                    FormatterFactory::create($params['format'])->setSource($this->navigationRepository)
                );
            }
        } catch (Exception $e) {
            $payload = new ExceptionPayload($e);
        }

        return $payload;

    }

}
