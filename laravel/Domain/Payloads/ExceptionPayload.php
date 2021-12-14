<?php

namespace App\Domain\Payloads;

use App\Base\Domain\Payloads\ErrorPayload;
use Exception;


class ExceptionPayload extends ErrorPayload
{

    public function getData()
    {
        if ($this->data instanceof Exception) {
            if (config('APP_ENV') !== 'production') {
                $error = [
                    'exception' => [
                        'message' => $this->data->getMessage(),
                        'class' => get_class($this->data),
                        'file' => $this->data->getFile(),
                        'line' => $this->data->getLine(),
                        'trace' => explode("\n", $this->data->getTraceAsString()),
                    ],
                ];
            } else {
                $error = $this->data->getMessage();
            }

            $this->data = $error;
        } else {
            $type = gettype($this->data);
            $this->data = [
                "Data in ExceptionPayload should be an instance of Exception but it is of type: '{$type}'",
            ];
        }

        return parent::getData();
    }

}
