<?php

namespace App\Http\Requests;

use App\Http\Traits\ApiResponser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class DQFormRequest extends FormRequest
{
    use ApiResponser;

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->validationError(
            $validator->errors()
        ));
    }
}
