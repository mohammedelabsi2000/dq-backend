<?php

namespace App\Http\Requests\DailyAchievement;

use App\Enums\AchievementStatus;
use App\Enums\AchievementType;
use App\Enums\EvaluationGrade;
use App\Enums\MemorizationDirection;
use App\Http\Requests\DQFormRequest;
use App\Services\QuranCalculatorService;

class StoreDailyAchievementRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'   => ['required', 'integer', 'exists:students,id'],
            // 'teacher_id'   => ['nullable', 'integer', 'exists:users,id'],
            'subject_id'   => ['nullable', 'integer', 'exists:subjects,id'],

            'date'         => ['required', 'date'],

            'from_surah'   => ['required', 'integer', 'exists:quran_surahs,id'],
            'from_ayah'    => ['required', 'integer', 'min:1'],
            'to_surah'     => ['required', 'integer', 'exists:quran_surahs,id'],
            'to_ayah'      => ['required', 'integer', 'min:1'],

            'memorization_direction' => ['nullable', 'string', 'in:' . implode(',', array_column(MemorizationDirection::cases(), 'value'))],

            'achievement_type'   => ['nullable', 'string', 'in:' . implode(',', AchievementType::getValues())],
            'evaluation_grade'   => ['nullable', 'string', 'in:' . implode(',', EvaluationGrade::getValues())],
            'achievement_status' => ['nullable', 'string', 'in:' . implode(',', AchievementStatus::getValues())],

            'mistakes_count' => ['nullable', 'integer', 'min:0'],
            'notes'          => ['nullable', 'string'],
            'recorded_at'    => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // التحقق من عدم تكرار الإنجاز لنفس الطالب في نفس اليوم
            $existingAchievement = \App\Models\DailyAchievement::where('student_id', $this->student_id)
                ->where('date', $this->date)
                ->when($this->route('daily_achievement'), function ($query) {
                    $query->where('id', '!=', $this->route('daily_achievement')->id);
                })
                ->exists();

            if ($existingAchievement) {
                $validator->errors()->add('student_id', 'يوجد إنجاز مسجل لهذا الطالب في هذا التاريخ بالفعل');
            }

            if ($validator->errors()->has('from_surah') || $validator->errors()->has('to_surah')
                || $validator->errors()->has('from_ayah') || $validator->errors()->has('to_ayah')) {
                return; // لا تتحقق من النطاق لو فيه أخطاء أساسية بالفعل
            }

            $errors = app(QuranCalculatorService::class)->validateRange(
                $this->from_surah,
                $this->from_ayah,
                $this->to_surah,
                $this->to_ayah
            );

            foreach ($errors as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }

    public function attributes(): array
    {
        return [
            'student_id'         => 'الطالب',
            'teacher_id'         => 'المعلم',
            'subject_id'         => 'المساق',
            'date'               => 'التاريخ',
            'from_surah'         => 'سورة البداية',
            'from_ayah'          => 'آية البداية',
            'to_surah'           => 'سورة النهاية',
            'to_ayah'            => 'آية النهاية',
            'memorization_direction' => 'اتجاه الحفظ',
            'achievement_type'   => 'نوع الحفظ',
            'evaluation_grade'   => 'درجة التقييم',
            'achievement_status' => 'حالة الإنجاز',
            'mistakes_count'     => 'عدد الأخطاء',
            'notes'              => 'الملاحظات',
            'recorded_at'        => 'وقت التسجيل',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'حقل الطالب مطلوب',
            'student_id.exists'   => 'الطالب المحدد غير موجود',
            'teacher_id.exists'   => 'المعلم المحدد غير موجود',
            'subject_id.exists'   => 'المساق المحدد غير موجود',

            'date.required' => 'حقل التاريخ مطلوب',
            'date.date'     => 'صيغة التاريخ غير صحيحة',

            'from_surah.required' => 'حقل سورة البداية مطلوب',
            'from_surah.exists'   => 'سورة البداية غير موجودة',
            'from_ayah.required'  => 'حقل آية البداية مطلوب',

            'to_surah.required' => 'حقل سورة النهاية مطلوب',
            'to_surah.exists'   => 'سورة النهاية غير موجودة',
            'to_ayah.required'  => 'حقل آية النهاية مطلوب',

            'memorization_direction.in' => 'اتجاه الحفظ يجب أن يكون تصاعدي أو تنازلي',

            'achievement_type.required'   => 'حقل نوع الحفظ مطلوب',
            'achievement_type.in'         => 'نوع الحفظ غير صحيح',
            'evaluation_grade.required'   => 'حقل درجة التقييم مطلوب',
            'evaluation_grade.in'         => 'درجة التقييم غير صحيحة',
            'achievement_status.required' => 'حقل حالة الإنجاز مطلوب',
            'achievement_status.in'       => 'حالة الإنجاز غير صحيحة',

            'mistakes_count.integer' => 'عدد الأخطاء يجب أن يكون رقماً',
            'mistakes_count.min'     => 'عدد الأخطاء يجب أن يكون أكبر من أو يساوي صفر',
        ];
    }
}