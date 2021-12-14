<?php

namespace App\Domain\Payloads;

use App\Base\Domain\Payloads\SuccessPayload;


class DataPayload extends SuccessPayload
{

    public function getData()
    {
        $data = parent::getData();

        return [
            'data' => $data
        ];
    }

}
