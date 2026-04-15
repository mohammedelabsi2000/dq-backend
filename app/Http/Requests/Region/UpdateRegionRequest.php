<?php

namespace App\Http\Requests\Region;

use App\Models\Branch;
use App\Models\Region;
use App\Http\Requests\DQFormRequest;

class UpdateRegionRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $region = $this->route('region'); // الحصول على المنطقة من الرابط

        // التحقق من صلاحية تعديل المنطقة الحالية
        if (!$this->user()->can('update', $region)) {
            return false;
        }

        // إذا تم تغيير الفرع، تحقق من صلاحية الإنشاء في الفرع الجديد
        if ($this->has('branch_id') && $this->input('branch_id') != $region->branch_id) {
            $newBranch = Branch::findOrFail($this->input('branch_id'));
            return $this->user()->can('create', [Region::class, $newBranch]);
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
            'branch_id' => 'sometimes|required|exists:branches,id',
            'notes'     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'اسم المنطقة مطلوب',
            'branch_id.required' => 'يجب اختيار الفرع',
            'branch_id.exists'   => 'الفرع المحدد غير موجود',
        ];
    }
}
