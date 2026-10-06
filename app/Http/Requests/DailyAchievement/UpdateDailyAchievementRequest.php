<?php

namespace App\Http\Requests\DailyAchievement;

use App\Enums\AchievementStatus;
use App\Enums\AchievementType;
use App\Enums\EvaluationGrade;
use App\Enums\MemorizationDirection;
use App\Http\Requests\DQFormRequest;
use App\Services\QuranCalculatorService;
use App\Services\SubjectProgressService;

class UpdateDailyAchievementRequest extends DQFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'   => ['sometimes', 'required', 'integer', 'exists:students,id'],
            'teacher_id'   => ['nullable', 'integer', 'exists:users,id'],
            'subject_id'   => ['nullable', 'integer', 'exists:subjects,id'],

            // لا يُقبل تاريخ مستقبلي (اليوم حسب التوقيت المحلي لأن توقيت التطبيق UTC)
            'date'         => ['sometimes', 'required', 'date', 'before_or_equal:' . now('Asia/Gaza')->toDateString()],

            'from_surah'   => ['sometimes', 'required', 'integer', 'exists:quran_surahs,id'],
            'from_ayah'    => ['sometimes', 'required', 'integer', 'min:1'],
            'to_surah'     => ['sometimes', 'required', 'integer', 'exists:quran_surahs,id'],
            'to_ayah'      => ['sometimes', 'required', 'integer', 'min:1'],

            'memorization_direction' => ['sometimes', 'nullable', 'string', 'in:' . implode(',', array_column(MemorizationDirection::cases(), 'value'))],

            'achievement_type'   => ['sometimes', 'nullable', 'string', 'in:' . implode(',', AchievementType::getValues())],
            'evaluation_grade'   => ['sometimes', 'nullable', 'string', 'in:' . implode(',', EvaluationGrade::getValues())],
            'achievement_status' => ['sometimes', 'nullable', 'string', 'in:' . implode(',', AchievementStatus::getValues())],

            'mistakes_count' => ['nullable', 'integer', 'min:0'],
            'notes'          => ['nullable', 'string'],
            'recorded_at'    => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $rangeFields = ['from_surah', 'from_ayah', 'to_surah', 'to_ayah'];
            $progressFields = [...$rangeFields, 'student_id', 'subject_id', 'achievement_type', 'achievement_status'];
            $achievement = $this->route('daily_memorization');

            // التحقق فقط لو تغيّر النطاق أو المادة أو نوع/حالة الإنجاز
            if (!$this->hasAny($progressFields) || $validator->errors()->hasAny($progressFields)) {
                return;
            }

            // الإنجاز مرتبط بسجل الطالب في المادة، فلا يُنقل لطالب آخر أو مادة أخرى
            foreach (['student_id', 'subject_id'] as $field) {
                if ($this->has($field) && (string) $this->input($field) !== (string) $achievement->getRawOriginal($field)) {
                    $validator->errors()->add($field, 'لا يمكن تغيير الطالب أو المادة لإنجاز مسجّل؛ احذف الإنجاز وأعد إدخاله');
                    return;
                }
            }

            // هذه الحقول تُعدَّل لآخر إنجاز في المادة فقط حتى لا تتكوّن فجوة في ترتيب الحفظ
            if ($achievement->hasLaterAchievements()) {
                foreach ($progressFields as $field) {
                    if ($this->has($field) && (string) $this->input($field) !== (string) $achievement->getRawOriginal($field)) {
                        $validator->errors()->add($field, 'لا يمكن تعديل نطاق أو مادة أو نوع أو حالة إنجاز تم تسجيل إنجازات بعده؛ التعديل متاح لآخر إنجاز في المادة فقط');
                        return;
                    }
                }
            }

            // القيم غير المُرسلة تؤخذ من الإنجاز الحالي
            [$fromSurah, $fromAyah, $toSurah, $toAyah] = array_map(
                fn($field) => (int) ($this->input($field) ?? $achievement->{$field}),
                $rangeFields
            );

            $progress = app(SubjectProgressService::class);
            $studentSubject = $achievement->studentSubject()->with('subject')->first();

            // التحقق من صحة النطاق العام حسب اتجاه الجزء الذي تقع فيه نقطة البداية
            $errors = app(QuranCalculatorService::class)->validateRange(
                $fromSurah,
                $fromAyah,
                $toSurah,
                $toAyah,
                $studentSubject && $progress->isDescending($progress->directionAt($studentSubject, $fromSurah, $fromAyah))
            );

            // التحقق من أن النطاق داخل المادة وغير مسجّل مسبقاً في إنجاز آخر
            if (!$errors && $studentSubject) {
                $errors = $progress->validateAchievementRange(
                    $studentSubject,
                    $this->input('achievement_type')
                        ?? $achievement->achievement_type?->value
                        ?? AchievementType::NEW_MEMORIZATION->value,
                    $fromSurah,
                    $fromAyah,
                    $toSurah,
                    $toAyah,
                    $achievement->id
                );
            }

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
            'date.before_or_equal' => 'لا يمكن إدخال تاريخ مستقبلي للإنجاز',

            'from_surah.exists' => 'سورة البداية غير موجودة',
            'to_surah.exists'   => 'سورة النهاية غير موجودة',

            'memorization_direction.in' => 'اتجاه الحفظ يجب أن يكون تصاعدي أو تنازلي',

            'achievement_type.in'   => 'نوع الحفظ غير صحيح',
            'evaluation_grade.in'   => 'درجة التقييم غير صحيحة',
            'achievement_status.in' => 'حالة الإنجاز غير صحيحة',

            'mistakes_count.integer' => 'عدد الأخطاء يجب أن يكون رقماً',
            'mistakes_count.min'     => 'عدد الأخطاء يجب أن يكون أكبر من أو يساوي صفر',
        ];
    }
}
