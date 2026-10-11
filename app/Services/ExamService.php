<?php

namespace App\Services;

use App\Enums\AchievementType;
use App\Enums\ResultStatus;
use App\Enums\SubjectType;
use App\Models\LevelTrackSubject;
use App\Models\RecitationExam;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\TrialExam;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * مسار الطالب في المادة بعد الحفظ:
 *   الحفظ (الإنجاز اليومي) ← السرد التجريبي ← الاختبار التجريبي ← الاختبار النهائي ← اعتماد نجاح المادة والانتقال للمادة التالية.
 *
 * الاختبار النهائي لم يُنفَّذ بعد: النجاح في الاختبار التجريبي ينقل الطالب لمرحلة final_exam فقط،
 * واعتماد المادة والانتقال للتالية (completeSubject) سيُستدعى من الاختبار النهائي.
 *
 * كل اختبار مرتبط بسجل الطالب في المادة (StudentSubject) فكل خطة مستقلة عن الأخرى.
 * حدود النجاح (الأخطاء/التنبيهات المسموحة وعلامة النجاح) تُنسخ من المادة عند إنشاء السجل.
 * المحاولة التي لم تُدخل نتيجتها بعد تمثّل "بانتظار"؛ وعند الرسوب تُفتح محاولة جديدة تلقائياً.
 */
class ExamService
{
    public const STAGE_MEMORIZATION = 'memorization';
    public const STAGE_TRIAL_RECITATION = 'trial_recitation';
    public const STAGE_TRIAL_EXAM = 'trial_exam';
    public const STAGE_FINAL_EXAM = 'final_exam';
    public const STAGE_COMPLETED = 'completed';

    public function __construct(
        protected SubjectProgressService $progress
    ) {}

    /**
     * مواد الحفظ هي التي تمر بالسرد والاختبار التجريبي
     */
    public function requiresExams(Subject $subject): bool
    {
        return in_array($subject->subject_type_id, [
            SubjectType::Memorization->id(),
            SubjectType::LimitedMemorization->id(),
        ]);
    }

    public function memorizationCompleted(StudentSubject $studentSubject): bool
    {
        return $this->progress->state($studentSubject, AchievementType::NEW_MEMORIZATION->value)['current'] === null;
    }

    /**
     * المرحلة الحالية للطالب في المادة
     */
    public function stage(StudentSubject $studentSubject): string
    {
        if ((int) $studentSubject->result_status_id === ResultStatus::Passed->id()) {
            return self::STAGE_COMPLETED;
        }

        if (!$this->requiresExams($studentSubject->subject) || !$this->memorizationCompleted($studentSubject)) {
            return self::STAGE_MEMORIZATION;
        }

        if ($studentSubject->latestRecitationExam?->status() !== RecitationExam::PASSED) {
            return self::STAGE_TRIAL_RECITATION;
        }

        return $studentSubject->latestTrialExam?->status() === TrialExam::PASSED
            ? self::STAGE_FINAL_EXAM
            : self::STAGE_TRIAL_EXAM;
    }

    // ========================
    // الربط مع الإنجاز اليومي
    // ========================

    /**
     * هل سُجّلت نتيجة سرد تجريبي للطالب في هذه المادة؟ بعدها لا يُعدَّل الحفظ.
     */
    public function recitationStarted(int $studentSubjectId): bool
    {
        return RecitationExam::where('student_subject_id', $studentSubjectId)->whereNotNull('errors_count')->exists();
    }

    /**
     * يُستدعى بعد أي تغيير في إنجازات الحفظ: عند اكتمال حفظ المادة يُنقل الطالب للسرد التجريبي
     * (تُفتح له محاولة بانتظار النتيجة)، ولو تراجع الاكتمال قبل إدخال أي نتيجة تُلغى المحاولة.
     */
    public function syncRecitationStage(StudentSubject $studentSubject): void
    {
        if (!$this->requiresExams($studentSubject->subject) || $this->recitationStarted($studentSubject->id)) {
            return;
        }

        $pending = RecitationExam::where('student_subject_id', $studentSubject->id);

        if (!$this->memorizationCompleted($studentSubject)) {
            $pending->forceDelete();
            return;
        }

        if (!$pending->exists()) {
            $this->openRecitation($studentSubject);
        }
    }

    // ========================
    // السرد التجريبي
    // ========================

    /**
     * تسجيل نتيجة السرد التجريبي على المحاولة المفتوحة (أو فتح محاولة جديدة بعد رسوب)
     */
    public function recordRecitation(StudentSubject $studentSubject, array $data): RecitationExam
    {
        return DB::transaction(function () use ($studentSubject, $data) {
            if (!$this->memorizationCompleted($studentSubject)) {
                $this->fail('student_id', 'لم يُكمل الطالب حفظ المادة بعد؛ السرد التجريبي يكون بعد انتهاء الحفظ');
            }

            $exam = $studentSubject->latestRecitationExam()->first();

            if ($exam?->status() === RecitationExam::PASSED) {
                $this->fail('student_id', 'الطالب ناجح في السرد التجريبي لهذه المادة؛ المرحلة التالية هي الاختبار التجريبي');
            }

            if (!$exam || $exam->hasResult()) {
                $exam = $this->openRecitation($studentSubject);
            }

            return $this->saveRecitation($exam, $data);
        });
    }

    public function updateRecitation(RecitationExam $exam, array $data): RecitationExam
    {
        return DB::transaction(function () use ($exam, $data) {
            $this->ensureRecitationEditable($exam);

            return $this->saveRecitation($exam, $data);
        });
    }

    public function deleteRecitation(RecitationExam $exam): void
    {
        DB::transaction(function () use ($exam) {
            $this->ensureRecitationEditable($exam);

            $studentSubject = $exam->studentSubject;
            $exam->delete();

            $this->clearPending($studentSubject, $exam->attempt_number);

            // الطالب ما زال في مرحلة السرد: تبقى له محاولة مفتوحة
            $latest = $studentSubject->latestRecitationExam()->first();

            if (!$latest || $latest->status() === RecitationExam::FAILED) {
                $this->openRecitation($studentSubject);
            }
        });
    }

    private function saveRecitation(RecitationExam $exam, array $data): RecitationExam
    {
        $subject = $exam->subject;

        $exam->fill($data);
        $exam->errors_count_allowed ??= $subject->errors_count;
        $exam->alerts_count_allowed ??= $subject->alerts_count;

        if ($exam->hasResult()) {
            if ($exam->errors_count_allowed === null || $exam->alerts_count_allowed === null) {
                $this->fail('subject_id', 'لم يُحدَّد عدد الأخطاء والتنبيهات المسموح بها في إعدادات المادة');
            }

            $exam->exam_date ??= now('Asia/Gaza')->toDateString();
        }

        $exam->save();

        // النتيجة تحدد ما بعدها: نجاح ← يُفتح الاختبار التجريبي، رسوب ← تُفتح محاولة سرد جديدة
        $studentSubject = $exam->studentSubject;
        $this->clearPending($studentSubject, $exam->attempt_number);

        match ($exam->status()) {
            RecitationExam::PASSED => $this->openTrial($studentSubject),
            RecitationExam::FAILED => $this->openRecitation($studentSubject),
            default => null,
        };

        return $exam;
    }

    private function ensureRecitationEditable(RecitationExam $exam): void
    {
        $laterResult = RecitationExam::where('student_subject_id', $exam->student_subject_id)
            ->where('attempt_number', '>', $exam->attempt_number)
            ->whereNotNull('errors_count')
            ->exists();

        if ($laterResult) {
            $this->fail('attempt_number', 'لا يمكن تعديل أو حذف محاولة سرد سُجّلت بعدها محاولة أخرى');
        }

        if (TrialExam::where('student_subject_id', $exam->student_subject_id)->whereNotNull('mark')->exists()) {
            $this->fail('attempt_number', 'لا يمكن تعديل أو حذف السرد التجريبي بعد تسجيل نتيجة الاختبار التجريبي');
        }
    }

    private function openRecitation(StudentSubject $studentSubject): RecitationExam
    {
        $subject = $studentSubject->subject;

        return RecitationExam::create([
            'student_id' => $studentSubject->student_id,
            'subject_id' => $studentSubject->subject_id,
            'student_subject_id' => $studentSubject->id,
            'attempt_number' => $this->nextAttempt(RecitationExam::class, $studentSubject->id),
            // تُنسخ من المادة عند إنشاء السجل
            'errors_count_allowed' => $subject->errors_count,
            'alerts_count_allowed' => $subject->alerts_count,
        ]);
    }

    // ========================
    // الاختبار التجريبي
    // ========================

    /**
     * تسجيل نتيجة الاختبار التجريبي على المحاولة المفتوحة (أو فتح محاولة جديدة بعد رسوب)
     */
    public function recordTrial(StudentSubject $studentSubject, array $data): TrialExam
    {
        return DB::transaction(function () use ($studentSubject, $data) {
            if ($studentSubject->latestRecitationExam()->first()?->status() !== RecitationExam::PASSED) {
                $this->fail('student_id', 'يجب أن ينجح الطالب في السرد التجريبي أولاً قبل الاختبار التجريبي');
            }

            $exam = $studentSubject->latestTrialExam()->first();

            if ($exam?->status() === TrialExam::PASSED) {
                $this->fail('student_id', 'الطالب ناجح في الاختبار التجريبي لهذه المادة');
            }

            if (!$exam || $exam->hasResult()) {
                $exam = $this->openTrial($studentSubject, true);
            }

            return $this->saveTrial($exam, $data);
        });
    }

    public function updateTrial(TrialExam $exam, array $data): TrialExam
    {
        return DB::transaction(function () use ($exam, $data) {
            $this->ensureTrialEditable($exam);

            return $this->saveTrial($exam, $data);
        });
    }

    public function deleteTrial(TrialExam $exam): void
    {
        DB::transaction(function () use ($exam) {
            $this->ensureTrialEditable($exam);

            $studentSubject = $exam->studentSubject;
            $exam->delete();

            // الطالب ما زال في مرحلة الاختبار: تبقى له محاولة مفتوحة
            TrialExam::where('student_subject_id', $studentSubject->id)->whereNull('mark')->forceDelete();
            $this->openTrial($studentSubject, true);
        });
    }

    private function saveTrial(TrialExam $exam, array $data): TrialExam
    {
        $exam->fill($data);
        $exam->success_mark ??= $exam->subject->success_mark;

        if ($exam->hasResult()) {
            if ($exam->success_mark === null) {
                $this->fail('subject_id', 'لم تُحدَّد علامة النجاح في إعدادات المادة');
            }

            $exam->exam_date ??= now('Asia/Gaza')->toDateString();
        }

        $exam->save();

        $studentSubject = $exam->studentSubject;

        TrialExam::where('student_subject_id', $studentSubject->id)
            ->where('attempt_number', '>', $exam->attempt_number)
            ->whereNull('mark')
            ->forceDelete();

        // رسوب ← تُفتح محاولة اختبار جديدة. النجاح ينقل الطالب لمرحلة الاختبار النهائي،
        // ولا تُعتمد المادة ولا يُنقل للمادة التالية إلا بعده.
        if ($exam->status() === TrialExam::FAILED) {
            $this->openTrial($studentSubject, true);
        }

        return $exam;
    }

    private function ensureTrialEditable(TrialExam $exam): void
    {
        if ((int) $exam->studentSubject?->result_status_id === ResultStatus::Passed->id()) {
            $this->fail('attempt_number', 'تم اعتماد نجاح الطالب في المادة؛ لا يمكن تعديل اختباره التجريبي أو حذفه');
        }

        $laterResult = TrialExam::where('student_subject_id', $exam->student_subject_id)
            ->where('attempt_number', '>', $exam->attempt_number)
            ->whereNotNull('mark')
            ->exists();

        if ($laterResult) {
            $this->fail('attempt_number', 'لا يمكن تعديل أو حذف محاولة اختبار سُجّلت بعدها محاولة أخرى');
        }
    }

    /**
     * فتح محاولة اختبار تجريبي بانتظار النتيجة. بدون $force لا تُفتح إن وُجدت محاولة سابقة.
     */
    private function openTrial(StudentSubject $studentSubject, bool $force = false): ?TrialExam
    {
        if (!$force && TrialExam::where('student_subject_id', $studentSubject->id)->exists()) {
            return null;
        }

        return TrialExam::create([
            'student_id' => $studentSubject->student_id,
            'subject_id' => $studentSubject->subject_id,
            'student_subject_id' => $studentSubject->id,
            'attempt_number' => $this->nextAttempt(TrialExam::class, $studentSubject->id),
            // تُنسخ من المادة عند إنشاء السجل
            'success_mark' => $studentSubject->subject->success_mark,
        ]);
    }

    // ========================
    // اعتماد المادة والانتقال
    // ========================

    /**
     * اعتماد نجاح الطالب في المادة ونقله للمادة التالية في مساره.
     * يُستدعى عند اجتياز الاختبار النهائي (لم يُنفَّذ بعد).
     */
    public function completeSubject(StudentSubject $studentSubject, ?float $grade, ?string $date = null): ?StudentSubject
    {
        $date ??= now('Asia/Gaza')->toDateString();

        $studentSubject->update([
            'result_status_id' => ResultStatus::Passed->id(),
            'grade' => $grade,
            'grade_date' => $date,
            'to_date' => $date,
        ]);

        return $this->enrollInNextSubject($studentSubject);
    }

    /**
     * المادة التالية في نفس مسار المستوى (حسب الترتيب). لا يتجاوز المستوى: الانتقال للمستوى التالي يبقى يدوياً.
     */
    public function enrollInNextSubject(StudentSubject $studentSubject): ?StudentSubject
    {
        $link = $this->levelTrackLink($studentSubject);

        if (!$link) {
            return null;
        }

        $links = LevelTrackSubject::where('level_track_id', $link->level_track_id)
            ->get()
            ->sortBy(fn($item) => [$item->order ?? PHP_INT_MAX, $item->id])
            ->values();

        foreach ($links->slice($links->search(fn($item) => $item->id === $link->id) + 1) as $next) {
            $existing = StudentSubject::where('student_id', $studentSubject->student_id)
                ->where('subject_id', $next->subject_id)
                ->where('level_id', $studentSubject->level_id)
                ->latest('id')
                ->first();

            // مادة أنجزها الطالب في هذا المستوى (مثلاً معادلة): ننتقل لما بعدها
            if ($existing && (int) $existing->result_status_id === ResultStatus::Passed->id()) {
                continue;
            }

            if ($existing) {
                return $existing;
            }

            $previousPass = StudentSubject::previousPass($studentSubject->student_id, $next->subject_id);

            return StudentSubject::create([
                'student_id' => $studentSubject->student_id,
                'subject_id' => $next->subject_id,
                'level_id' => $studentSubject->level_id,
                'result_status_id' => ResultStatus::InProgress->id(),
                'from_date' => now('Asia/Gaza')->toDateString(),
                'notes' => $previousPass
                    ? "تنبيه: الطالب أنجز هذا المساق سابقاً (سجل رقم {$previousPass->id}) - يُراجَع للإعادة أو الإعفاء"
                    : null,
            ]);
        }

        return null;
    }

    /**
     * المادة التي انتقل إليها الطالب بعد هذه المادة (إن وجدت)
     */
    public function nextStudentSubject(StudentSubject $studentSubject): ?StudentSubject
    {
        $link = $this->levelTrackLink($studentSubject);

        if (!$link) {
            return null;
        }

        return StudentSubject::where('student_id', $studentSubject->student_id)
            ->where('level_id', $studentSubject->level_id)
            ->where('id', '>', $studentSubject->id)
            ->whereIn('subject_id', LevelTrackSubject::where('level_track_id', $link->level_track_id)->pluck('subject_id'))
            ->with('subject')
            ->oldest('id')
            ->first();
    }

    // ========================
    // Helpers
    // ========================

    private function levelTrackLink(StudentSubject $studentSubject): ?LevelTrackSubject
    {
        return LevelTrackSubject::where('subject_id', $studentSubject->subject_id)
            ->whereHas('levelTrack', fn($q) => $q->where('level_id', $studentSubject->level_id))
            ->first();
    }

    /**
     * حذف المحاولات التي لم تُدخل نتيجتها بعد محاولة معيّنة، والاختبار التجريبي المفتوح بلا نتيجة
     */
    private function clearPending(StudentSubject $studentSubject, int $afterAttempt): void
    {
        RecitationExam::where('student_subject_id', $studentSubject->id)
            ->where('attempt_number', '>', $afterAttempt)
            ->whereNull('errors_count')
            ->forceDelete();

        TrialExam::where('student_subject_id', $studentSubject->id)->whereNull('mark')->forceDelete();
    }

    /**
     * رقم المحاولة التالية (يشمل المحذوفة حتى لا يتكرر الرقم)
     */
    private function nextAttempt(string $model, int $studentSubjectId): int
    {
        return (int) $model::withTrashed()->where('student_subject_id', $studentSubjectId)->max('attempt_number') + 1;
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
