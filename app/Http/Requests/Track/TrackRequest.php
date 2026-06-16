<?php

namespace App\Http\Requests\Track;

use App\Models\Track;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class TrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        $method = $this->getMethod();
        if ($method === 'POST') {
            return $this->user()->can('create', Track::class);
            // return $this->user()->hasPermissionTo('tracks.create');
        } elseif (in_array($method, ['PUT', 'PATCH'])) {
            return $this->user()->can('update', $this->route('track'));
            // return $this->user()->hasPermissionTo('tracks.update');
        }
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
