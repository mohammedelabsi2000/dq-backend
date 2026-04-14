<?php

namespace App\Http\Requests;

use App\Http\Traits\ApiResponser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DQFormRequest extends FormRequest
{
    use ApiResponser;

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error('حدث خطأ في التحقق من البيانات', 422, $validator->errors())
        );
    }
}