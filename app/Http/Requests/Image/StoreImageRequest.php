<?php

namespace App\Http\Requests\Image;

use App\Enums\ImageType;
use Illuminate\Validation\Rules\Enum;
use App\Http\Requests\DQFormRequest;

class StoreImageRequest extends DQFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image' => 'required|mimes:jpeg,jpg,png,gif,pdf|max:2048',
            'imageable_id' => 'required|integer',
            'imageable_type' => 'required|string',
            'image_type' => ['nullable', 'string', new Enum(ImageType::class)],
            'is_main' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'الصورة مطلوبة',
            'image.mimes' => 'الصورة يجب أن تكون من نوع jpeg, jpg, png, gif, pdf',
            'image.max' => 'الصورة يجب أن تكون أقل من 2MB',
            'imageable_id.required' => 'معرف الكائن المتعلق مطلوب',
            'imageable_type.required' => 'نوع الكائن المتعلق مطلوب',
            'image_type.string' => 'نوع الصورة يجب أن يكون نص',
            'image_type.enum' => 'نوع الصورة يجب أن يكون من القائمة المحددة',
            'is_main.boolean' => 'الصورة الرئيسية يجب أن تكون True أو False',
            'notes.string' => 'الملاحظات يجب أن تكون نص',
        ];
    }
}
