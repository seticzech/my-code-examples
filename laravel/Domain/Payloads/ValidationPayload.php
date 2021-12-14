<?php

namespace App\Domain\Payloads;

use App\Base\Domain\Payloads\ErrorPayload;
use Illuminate\Contracts\Validation\Validator;


class ValidationPayload extends ErrorPayload
{

    public function getData()
    {
        if ($this->data instanceof Validator) {
            $this->data = $this->data->getMessageBag();
        }

        return parent::getData();
    }

}
