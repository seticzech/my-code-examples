<?php

namespace App\Api\Responders\Base;

use App\Base\Responders\JsonResponder;
use App\Domain\Entities\File;


class DownloadResponder extends JsonResponder
{

    public function respond()
    {
        $response = $this->getResponse();

        if ($response instanceof File) {
            $headers = [
                'Content-Type' => $response->getMimeType(),
            ];

            return response()->download($response->getRealName(), $response->getName(), $headers);
        }

        return parent::respond();
    }

}
