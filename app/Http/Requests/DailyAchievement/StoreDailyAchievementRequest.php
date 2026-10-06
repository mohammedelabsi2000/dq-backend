<?php

namespace App\Http\Requests\DailyAchievement;

use App\Enums\AchievementStatus;
use App\Enums\AchievementType;
use App\Enums\EvaluationGrade;
use App\Enums\MemorizationDirection;
use App\Http\Requests\DQFormRequest;
use App\Models\StudentSubject;
use App\Services\QuranCalculatorService;
use App\Services\SubjectProgressService;

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
            'subject_id'   => ['required', 'integer', 'exists:subjects,id'],
            // سجل الطالب في المادة: يُحدَّد تلقائياً، ويلزم إرساله فقط لو كانت المادة للطالب في أكثر من خطة
            'student_subject_id' => ['nullable', 'integer'],

            // لا يُقبل تاريخ مستقبلي (اليوم حسب التوقيت المحلي لأن توقيت التطبيق UTC)
            'date'         => ['required', 'date', 'before_or_equal:' . now('Asia/Gaza')->toDateString()],

            // نقطة البداية لا يُدخلها المستخدم: تُحسب تلقائياً في prepareForValidation
            'from_surah'   => ['nullable', 'integer', 'exists:quran_surahs,id'],
            'from_ayah'    => ['nullable', 'integer', 'min:1'],
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

    /**
     * سجل الطالب في المادة الذي سيُسجَّل عليه الإنجاز
     */
    protected ?StudentSubject $studentSubject = null;

    /**
     * خطأ تحديد سجل المادة أو نقطة البداية تلقائياً (إن وجد): [الحقل، الرسالة]
     */
    protected ?array $resolveError = null;

    /**
     * المستخدم يُدخل نقطة النهاية فقط؛ البداية تُحسب من آخر ما سُجّل للطالب في المادة
     * حسب اتجاه الجزء الحالي (تصاعدي/تنازلي)، وأي بداية مُرسلة يتم تجاهلها.
     */
    protected function prepareForValidation(): void
    {
        $this->getInputSource()->remove('from_surah');
        $this->getInputSource()->remove('from_ayah');

        if (!$this->isInt('student_id') || !$this->isInt('subject_id')) {
            return;
        }

        $progress = app(SubjectProgressService::class);

        $resolved = $progress->resolveStudentSubject(
            (int) $this->input('student_id'),
            (int) $this->input('subject_id'),
            $this->isInt('student_subject_id') ? (int) $this->input('student_subject_id') : null
        );

        if (isset($resolved['error'])) {
            $this->resolveError = [$resolved['field'], $resolved['error']];
            return;
        }

        $this->studentSubject = $resolved['student_subject'];
        $this->merge(['student_subject_id' => $this->studentSubject->id]);

        if (!$this->isInt('to_surah') || !$this->isInt('to_ayah')) {
            return;
        }

        $range = $progress->resolveRange(
            $this->studentSubject,
            $this->achievementType(),
            (int) $this->input('to_surah'),
            (int) $this->input('to_ayah')
        );

        if (isset($range['error'])) {
            $this->resolveError = ['to_surah', $range['error']];
            return;
        }

        $this->merge($range['range']);
    }

    private function isInt(string $field): bool
    {
        return filter_var($this->input($field), FILTER_VALIDATE_INT) !== false;
    }

    private function achievementType(): string
    {
        return (AchievementType::tryFrom((string) $this->input('achievement_type'))
            ?? AchievementType::NEW_MEMORIZATION)->value;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->hasAny(['student_id', 'subject_id', 'student_subject_id', 'from_surah', 'from_ayah', 'to_surah', 'to_ayah'])) {
                return; // لا تتحقق من النطاق لو فيه أخطاء أساسية بالفعل
            }

            $progress = app(SubjectProgressService::class);

            if ($progress->activePlans($this->student_id)->isEmpty()) {
                $validator->errors()->add('student_id', 'لا توجد خطة نشطة لهذا الطالب');
                return;
            }

            if ($this->resolveError) {
                $validator->errors()->add(...$this->resolveError);
                return;
            }

            if (!$this->studentSubject || !$this->filled('from_surah')) {
                $validator->errors()->add('to_surah', 'تعذّر تحديد نقطة بداية الإنجاز');
                return;
            }

            // التحقق من عدم تكرار الإنجاز لنفس الطالب في نفس المادة (في نفس الخطة) في نفس اليوم
            $existingAchievement = \App\Models\DailyAchievement::where('student_subject_id', $this->studentSubject->id)
                ->where('date', $this->date)
                ->exists();

            if ($existingAchievement) {
                $validator->errors()->add('student_id', 'يوجد إنجاز مسجل لهذا الطالب في هذه المادة في هذا التاريخ بالفعل');
                return;
            }

            // التحقق من صحة النطاق العام حسب اتجاه الجزء الذي تقع فيه نقطة البداية
            $errors = app(QuranCalculatorService::class)->validateRange(
                $this->from_surah,
                $this->from_ayah,
                $this->to_surah,
                $this->to_ayah,
                $progress->isDescending($progress->directionAt($this->studentSubject, $this->from_surah, $this->from_ayah))
            );

            // التحقق من أن النطاق داخل المادة وغير مسجّل مسبقاً ولا يعبر إلى جزء مختلف الاتجاه
            $errors = $errors ?: $progress->validateAchievementRange(
                $this->studentSubject,
                $this->achievementType(),
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
            'subject_id.required' => 'حقل المساق مطلوب',
            'subject_id.exists'   => 'المساق المحدد غير موجود',

            'date.required' => 'حقل التاريخ مطلوب',
            'date.date'     => 'صيغة التاريخ غير صحيحة',
            'date.before_or_equal' => 'لا يمكن إدخال تاريخ مستقبلي للإنجاز',

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
