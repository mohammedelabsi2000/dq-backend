<?php

namespace App\Http\Requests\DailyAchievement;

use App\Enums\AchievementStatus;
use App\Enums\AchievementType;
use App\Enums\EvaluationGrade;
use App\Models\Quran\Surah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyAchievementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => 'sometimes|required|exists:students,id',
            'teacher_id' => 'nullable|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date' => 'sometimes|required|date',
            'from_surah' => 'sometimes|required|integer|min:1|max:114|exists:quran_surahs,id',
            'from_ayah' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->from_surah) {
                        $surah = Surah::find($this->from_surah);
                        if ($surah && $value > $surah->verses_count) {
                            $fail('رقم الآية البداية يجب أن لا يتجاوز ' . $surah->verses_count . ' في سورة ' . $surah->name_ar);
                        }
                    }
                },
            ],
            'to_surah' => 'sometimes|required|integer|min:1|max:114|exists:quran_surahs,id',
            'to_ayah' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->to_surah) {
                        $surah = Surah::find($this->to_surah);
                        if ($surah && $value > $surah->verses_count) {
                            $fail('رقم الآية النهاية يجب أن لا يتجاوز ' . $surah->verses_count . ' في سورة ' . $surah->name_ar);
                        }
                    }
                },
            ],
            'achievement_type' => ['sometimes', 'required', Rule::in(AchievementType::getValues())],
            'evaluation_grade' => ['sometimes', 'required', Rule::in(EvaluationGrade::getValues())],
            'achievement_status' => ['sometimes', 'required', Rule::in(AchievementStatus::getValues())],
            'mistakes_count' => 'sometimes|required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'حقل الطالب مطلوب',
            'student_id.exists' => 'الطالب المحدد غير موجود',
            'teacher_id.exists' => 'المعلم المحدد غير موجود',
            'subject_id.exists' => 'المادة المحددة غير موجودة',
            'date.required' => 'حقل التاريخ مطلوب',
            'date.date' => 'التاريخ يجب أن يكون صحيحاً',
            'from_surah.required' => 'حقل من سورة مطلوب',
            'from_surah.min' => 'رقم السورة يجب أن يكون بين 1 و 114',
            'from_surah.max' => 'رقم السورة يجب أن يكون بين 1 و 114',
            'from_ayah.required' => 'حقل من آية مطلوب',
            'from_ayah.min' => 'رقم الآية يجب أن يكون أكبر من 0',
            'to_surah.required' => 'حقل إلى سورة مطلوب',
            'to_surah.min' => 'رقم السورة يجب أن يكون بين 1 و 114',
            'to_surah.max' => 'رقم السورة يجب أن يكون بين 1 و 114',
            'to_ayah.required' => 'حقل إلى آية مطلوب',
            'to_ayah.min' => 'رقم الآية يجب أن يكون أكبر من 0',
            'ayah_count.required' => 'حقل عدد الآيات مطلوب',
            'ayah_count.min' => 'عدد الآيات يجب أن يكون أكبر من 0',
            'achievement_type.required' => 'حقل نوع الحفظ مطلوب',
            'evaluation_grade.required' => 'حقل درجة التقييم مطلوب',
            'achievement_status.required' => 'حقل حالة الإنجاز مطلوب',
            'mistakes_count.required' => 'حقل عدد الأخطاء مطلوب',
            'mistakes_count.min' => 'عدد الأخطاء يجب أن يكون 0 أو أكبر',
            'notes.max' => 'الملاحظات يجب أن لا تتجاوز 1000 حرف',
        ];
    }
}
