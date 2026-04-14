<?php

namespace App\Http\Requests\Center;

use App\Http\Traits\ApiResponser;
use App\Models\Center;
use App\Models\Region;
use App\Http\Requests\DQFormRequest;

class UpdateCenterRequest extends DQFormRequest
{
    use ApiResponser;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $center = $this->route('center'); // الحصول على المركز من الرابط

        // التحقق من صلاحية تعديل المركز الحالي
        if (!$this->user()->can('update', $center)) {
            return false;
        }

        // إذا تم تغيير المنطقة، تحقق من صلاحية الإنشاء في المنطقة الجديدة
        if ($this->has('region_id') && $this->input('region_id') != $center->region_id) {
            $newRegion = Region::findOrFail($this->input('region_id'));
            return $this->user()->can('create', [Center::class, $newRegion]);
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
        // return [
        //     'name' => ['sometimes', 'required', 'string', 'max:255'],
        //     'notes' => ['sometimes', 'nullable', 'string'],
        //     'mosque_id' => ['sometimes', 'required', 'integer', 'exists:mosques,id'],
        // ];
        return [
            'name'      => 'sometimes|required|string|max:255',
            'region_id' => 'sometimes|required|exists:regions,id',
            'mosque_id' => 'nullable|exists:mosques,id',
            'notes'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المركز مطلوب',
            'region_id.required' => 'يجب اختيار المنطقة',
            'region_id.exists'   => 'المنطقة المحددة غير موجودة',
            'mosque_id.exists'   => 'المسجد المحدد غير موجود',
        ];
    }
}
