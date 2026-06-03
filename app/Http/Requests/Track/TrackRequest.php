<?php

namespace App\Http\Requests\Track;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class TrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم المسار مطلوب.',
            'name.string'   => 'اسم المسار يجب أن يكون نصًا.',
            'name.max'      => 'اسم المسار لا يجب أن يتجاوز 255 حرفًا.',
            'notes.string'  => 'الملاحظات يجب أن تكون نصًا.',
        ];
    }
    
    public function attributes(): array
    {
        return [
            'name'  => 'اسم المسار',
            'notes' => 'الملاحظات',
        ];
    }
}
