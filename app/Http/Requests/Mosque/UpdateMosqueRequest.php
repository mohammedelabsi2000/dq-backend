<?php

namespace App\Http\Requests\Mosque;

use App\Http\Traits\ApiResponser;
use App\Models\Mosque;
use App\Models\Region;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMosqueRequest extends FormRequest
{
    use ApiResponser;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $mosque = $this->route('mosque'); // الحصول على المسجد من الرابط

        // التحقق من صلاحية تعديل المسجد الحالي
        if (!$this->user()->can('update', $mosque)) {
            return false;
        }

        // إذا تم تغيير المنطقة، تحقق من صلاحية الإنشاء في المنطقة الجديدة
        if ($this->has('region_id') && $this->input('region_id') != $mosque->region_id) {
            $newRegion = Region::findOrFail($this->input('region_id'));
            return $this->user()->can('create', [Mosque::class, $newRegion]);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'      => 'sometimes|required|string|max:255',
            'region_id' => 'sometimes|required|exists:regions,id',
            'notes'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المسجد مطلوب',
            'region_id.required' => 'يجب اختيار المنطقة',
            'region_id.exists'   => 'المنطقة المحددة غير موجودة',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $this->validationError([
            $validator->errors(),
        ]);
    }
}
