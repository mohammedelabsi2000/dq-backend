<?php

namespace App\Http\Requests\Level;

use App\Http\Requests\DQFormRequest;
use App\Models\Level;
use Illuminate\Validation\Rule;

class StoreLevelRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
        // return $this->user()->can('create', Level::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        logger($this->get('tracks'));
        // logger($this->get('tracks')[0]['order']);
        return [
            'plan_id' => 'required|integer|exists:plans,id',
            'name' => 'required|string|max:255',
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('levels', 'order')->where(function ($query) {
                    return $query->where('plan_id', $this->input('plan_id'));
                }),
            ],
            'period_unit' => 'required|in:day,week,month,year',
            'period' => 'required|integer|min:1',
            'min_period' => 'nullable|integer|min:1|lte:period',
            'max_period' => 'nullable|integer|min:1|gte:period',
            'notes' => 'nullable|string',
            'tracks' => 'nullable|array',
            // 'tracks.*.track_id' => 'required|integer|exists:tracks,id',
            'tracks.*.track_id' => 'required|integer|exists:tracks,id|distinct',
            'tracks.*.weight' => 'required|numeric|min:0|max:100',
            'tracks.*.order' => 'required|integer|min:1',
        ];
    }

    /**
     * Custom error messages for validation
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'plan_id.required' => 'حقل الخطة مطلوب',
            'plan_id.exists' => 'الخطة المحددة غير موجودة',
            'name.required' => 'حقل اسم المستوى مطلوب',
            'name.max' => 'اسم المستوى يجب ألا يتجاوز 255 حرفاً',
            'order.required' => 'حقل الترتيب مطلوب',
            'order.min' => 'الترتيب يجب أن يكون رقماً موجباً',
            'order.unique' => 'الترتيب مستخدم بالفعل ضمن هذه الخطة، يرجى اختيار ترتيب آخر',
            'period_unit.required' => 'حقل وحدة المدة مطلوب',
            'period_unit.in' => 'وحدة المدة يجب أن تكون: يوم، أسبوع، شهر، أو سنة',
            'period.required' => 'حقل المدة مطلوب',
            'period.min' => 'المدة يجب أن تكون رقماً موجباً',
            'min_period.min' => 'أقل مدة يجب أن تكون رقماً موجباً',
            'min_period.lte' => 'أقل مدة يجب أن تكون أقل من أو تساوي المدة الافتراضية',
            'max_period.min' => 'أقصى مدة يجب أن تكون رقماً موجباً',
            'max_period.gte' => 'أقصى مدة يجب أن تكون أكبر من أو تساوي المدة الافتراضية',
            'tracks.array' => 'حقل المسارات يجب أن يكون مصفوفة',
            'tracks.*.track_id.required' => 'حقل معرف المسار مطلوب',
            'tracks.*.track_id.exists' => 'المسار المحدد غير موجود',
            'tracks.*.track_id.distinct' => 'لا يمكن تكرار نفس المسار ضمن المستوى الواحد',
            'tracks.*.weight.required' => 'حقل وزن المسار مطلوب',
            'tracks.*.weight.min' => 'وزن المسار يجب أن يكون رقماً موجباً',
            'tracks.*.weight.max' => 'وزن المسار يجب أن لا يتجاوز 100',
            'tracks.*.order.required' => 'حقل ترتيب المسار مطلوب',
            'tracks.*.order.min' => 'ترتيب المسار يجب أن يكون رقماً موجباً',
        ];
    }
}
