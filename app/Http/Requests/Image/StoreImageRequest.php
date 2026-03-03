<?php

namespace App\Http\Requests\Image;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image' => 'required|image|max:2048',
            'imageable_id' => 'required|integer',
            'imageable_type' => 'required|string',
            'image_type' => 'nullable|string',
            'is_main' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }
}
