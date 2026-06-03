<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'track_id'    => ['required', 'exists:tracks,id'],
            'name'        => ['required', 'string', 'max:255', 'unique:subjects,name,' . $this->route('subject')],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'track_id.required' => 'المسار مطلوب.',
            'track_id.exists'   => 'المسار المحدد غير موجود.',
            'name.required'     => 'اسم المساق مطلوب.',
            'name.string'       => 'اسم المساق يجب أن يكون نصًا.',
            'name.max'          => 'اسم المساق لا يجب أن يتجاوز 255 حرفًا.',
            'description.string'=> 'الوصف يجب أن يكون نصًا.',
        ];
    }

    public function attributes(): array
    {
        return [
            'track_id'    => 'المسار',
            'name'        => 'اسم المساق',
            'description' => 'الوصف',
        ];
    }
}
