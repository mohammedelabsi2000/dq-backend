<?php

namespace App\Services;

use App\Enums\ResultStatus;
use App\Models\ExamCommittee;
use App\Models\FinalExam;
use App\Models\HalaqaStudent;
use App\Models\QualitySyncLog;
use App\Models\Scopes\GenderVisibilityScope;
use App\Models\StudentSubject;
use App\Models\User;
use App\Models\UserScope;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * الربط مع برنامج الجودة (دفعات الدورات في AFP: /api/v2/external/course-batches):
 *   - اعتماد اللجنة = إنشاء دفعة (sendCommittee).
 *   - زر التحديث = جلب الدفعة وتطبيق حالتها وعلامات طلابها (syncCommittee).
 *
 * المساق يُطابق بـ subjects.standard_subject_id، والفرع والمحلية بـ standard_branch_id / standard_region_id،
 * والطالب برقم هويته (الجودة لا تقبل نفس الطالب مرتين في الدفعة).
 * العلامات تصل للجنة دفعة واحدة ولا تُعدَّل بعد ذلك.
 */
class QualityService
{
    public function __construct(
        protected ExamService $exams
    ) {}

    // ========================
    // الإرسال (اعتماد اللجنة)
    // ========================

    /**
     * ينشئ الدفعة في الجودة ويحفظ رقمها وحالتها على اللجنة.
     *
     * @throws ValidationException بيانات ناقصة عندنا، أو رفض الجودة للدفعة
     * @throws \RuntimeException تعذّر الاتصال
     */
    public function sendCommittee(ExamCommittee $committee, User $requester): void
    {
        $this->ensureSubmissionOpen();

        $payload = $this->batchPayload($committee, $requester);
        $response = $this->request('post', 'course-batches', $committee->id, $payload);

        if ($response->status() === 422) {
            throw ValidationException::withMessages($this->rejectionMessages($response));
        }

        $this->ensureSuccessful($response);

        $this->applyBatch($committee, $response->json('data') ?? []);
        $committee->sent_at = now();
        $committee->save();
    }

    /**
     * يتحقق أن الجودة تستقبل الدفعات اليوم (on / off / scheduled)
     */
    public function ensureSubmissionOpen(): void
    {
        $response = $this->request('get', 'course-batches/submission-status');
        $this->ensureSuccessful($response);

        if (!$response->json('data.open')) {
            $days = $response->json('data.schedule_days');
            $names = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

            throw ValidationException::withMessages(['committee' => 'استقبال الدفعات في برنامج الجودة مغلق حالياً'
                . ($days ? '؛ الأيام المسموحة: ' . collect($days)->map(fn($d) => $names[$d] ?? $d)->implode('، ') : '')]);
        }
    }

    /**
     * جسم طلب إنشاء الدفعة. يجمع كل النواقص في رسالة واحدة قبل الإرسال.
     */
    public function batchPayload(ExamCommittee $committee, User $requester): array
    {
        $committee->loadMissing(['subject', 'branch', 'region', 'teacher']);

        $studentSubjects = StudentSubject::with(['student' => fn($q) => $q->withoutGlobalScope(GenderVisibilityScope::class), 'subject'])
            ->whereHas('examCommitteeStudents', fn($q) => $q->where('exam_committee_id', $committee->id))
            ->get();

        $errors = [];
        $missing = function (string $key, string $message) use (&$errors) {
            $errors[$key] = $message;
        };

        if (blank($requester->identity)) $missing('added_by', 'لا يوجد رقم هوية لحسابك؛ الجودة تشترط رقم هوية منشئ الدفعة');
        if (!$committee->branch?->standard_branch_id) $missing('branch_id', 'حدّد فرع اللجنة، ويجب أن يكون مربوطاً برقمه في النظام المعياري');
        if (!$committee->region?->standard_region_id) $missing('region_id', 'حدّد محلية اللجنة، ويجب أن تكون مربوطة برقمها في النظام المعياري');
        if (!$committee->proposed_exam_date) $missing('proposed_exam_date', 'موعد الاختبار المقترح مطلوب للإرسال للجودة');
        if (blank($committee->exam_location)) $missing('exam_location', 'مكان عقد الاختبار مطلوب للإرسال للجودة');

        $students = [];
        $teachers = [];

        foreach ($studentSubjects as $ss) {
            $name = $ss->student?->full_name ?? "سجل {$ss->id}";
            $teacher = $this->teacherIdentity($ss);
            $teachers[] = $teacher;

            if (blank($ss->student?->identity)) $missing("students.{$ss->id}", "الطالب {$name} بلا رقم هوية");

            $row = ['id_num' => (string) $ss->student?->identity];

            if ($committee->is_multi_subject) {
                if (!$ss->subject?->standard_subject_id) $missing("subject.{$ss->subject_id}", "مساق \"{$ss->subject?->title}\" غير مربوط بدورة في الجودة (standard_subject_id)");
                if (!$teacher) $missing("teacher.{$ss->id}", "لم يُعثر على محفّظ للطالب {$name} (حلقة فعّالة لها محفّظ)");

                $row += ['course_id' => $ss->subject?->standard_subject_id, 'teacher_id_num' => $teacher];
            }

            $students[] = $row;
        }

        $payload = [
            'added_by_id_num' => (string) $requester->identity,
            'multi_course' => $committee->is_multi_subject ? 1 : 0,
            'area_id' => $committee->branch?->standard_branch_id,
            'sub_area_id' => $committee->region?->standard_region_id,
            'gender' => $committee->gender?->value === 'ذكر' ? 'male' : 'female',
            'start_date' => $committee->start_date?->format('Y-m-d'),
            'suggested_exam_datetime' => $committee->proposed_exam_date?->format('Y-m-d H:i:s'),
            'exam_address' => $committee->exam_location,
            'students' => $students,
        ];

        if (!$committee->is_multi_subject) {
            // محفّظ اللجنة، وإلا المحفّظ المشترك لكل طلابها
            $teacher = $committee->teacher?->identity ?? (count(array_unique($teachers)) === 1 ? $teachers[0] : null);

            if (!$committee->subject?->standard_subject_id) $missing('subject_id', "مساق اللجنة \"{$committee->subject?->title}\" غير مربوط بدورة في الجودة (standard_subject_id)");
            if (!$teacher) $missing('teacher_id', 'حدّد محفّظ اللجنة (طلابها ليسوا عند محفّظ واحد أو بلا محفّظ)');

            $payload += ['course_id' => $committee->subject?->standard_subject_id, 'teacher_id_num' => $teacher];
        }

        // معالج الأخطاء في المشروع يعرض أول رسالة فقط، فتُجمع النواقص كلها في رسالة واحدة
        if ($errors) {
            throw ValidationException::withMessages(['committee' => 'لا يمكن الإرسال للجودة: ' . implode(' • ', $errors)]);
        }

        return $payload;
    }

    // ========================
    // التحديث من الجودة (زر)
    // ========================

    /**
     * يجلب الدفعة من الجودة ويطبّق حالتها وعلامات طلابها
     *
     * @return array ملخص: الحالة، وعدد النتائج الجديدة، والمنتظرين، والأخطاء
     */
    public function syncCommittee(ExamCommittee $committee): array
    {
        if (!$committee->standard_committee_id) {
            throw ValidationException::withMessages(['committee' => 'اللجنة لم تُرسل للجودة بعد']);
        }

        $response = $this->request('get', "course-batches/{$committee->standard_committee_id}", $committee->id);

        if ($response->status() === 404) {
            throw ValidationException::withMessages(['committee' => 'الدفعة غير موجودة في برنامج الجودة']);
        }

        $this->ensureSuccessful($response);
        $batch = $response->json('data') ?? [];

        $summary = ['status' => null, 'created' => 0, 'unchanged' => 0, 'pending' => 0, 'errors' => []];

        DB::transaction(function () use ($committee, $batch, &$summary) {
            $this->applyBatch($committee, $batch);
            $committee->save();
            $summary['status'] = $committee->status;

            $assignments = StudentSubject::with(['student' => fn($q) => $q->withoutGlobalScope(GenderVisibilityScope::class), 'subject'])
                ->whereHas('examCommitteeStudents', fn($q) => $q->where('exam_committee_id', $committee->id))
                ->get()
                ->keyBy(fn($ss) => (string) $ss->student?->identity);

            foreach ($batch['students'] ?? [] as $row) {
                $studentSubject = $assignments->get((string) ($row['id_num'] ?? ''));

                if (!$studentSubject) {
                    $summary['errors'][] = "الطالب برقم الهوية {$row['id_num']} في دفعة الجودة غير منسَّب لهذه اللجنة";
                    continue;
                }

                $summary[$this->applyMark($committee, $studentSubject, $row, $batch)]++;
            }
        });

        return $summary;
    }

    /**
     * @return string created|unchanged|pending
     */
    private function applyMark(ExamCommittee $committee, StudentSubject $studentSubject, array $row, array $batch): string
    {
        if (!isset($row['mark']) || $row['mark'] === '') {
            return 'pending';
        }

        // العلامات لا تُعدَّل في الجودة بعد إرسالها: ما سُجّل مرة لا يتغيّر
        if (FinalExam::where('student_subject_id', $studentSubject->id)->where('exam_committee_id', $committee->id)->exists()) {
            return 'unchanged';
        }

        $mark = (float) $row['mark'];
        // علامة النجاح: من الدفعة (مساق واحد)، وإلا من ربط المساق بالجودة، وإلا علامة نجاح المادة عندنا
        $passMark = $committee->is_multi_subject ? null : ($batch['pass_mark'] ?? null);
        $passMark ??= $studentSubject->subject?->standard_pass_mark ?? $studentSubject->subject?->success_mark;
        $passMark = $passMark !== null ? (float) $passMark : null;

        $exam = FinalExam::create([
            'student_subject_id' => $studentSubject->id,
            'student_id' => $studentSubject->student_id,
            'subject_id' => $studentSubject->subject_id,
            'exam_committee_id' => $committee->id,
            'attempt_number' => (int) FinalExam::withTrashed()->where('student_subject_id', $studentSubject->id)->max('attempt_number') + 1,
            'mark' => $mark,
            'success_mark' => $passMark,
            'result' => $passMark === null || $mark >= $passMark ? FinalExam::PASSED : FinalExam::FAILED,
            'exam_date' => isset($batch['exam_datetime']) ? date('Y-m-d', strtotime($batch['exam_datetime'])) : null,
            'notes' => isset($row['rate']) ? "التقدير: {$row['rate']}" : null,
        ]);

        if ($exam->isPassed() && (int) $studentSubject->result_status_id !== ResultStatus::Passed->id()) {
            $this->exams->completeSubject($studentSubject, $exam->mark, $exam->exam_date?->format('Y-m-d'));
        }

        return 'created';
    }

    /**
     * رقم الدفعة وحالتها وموعد الاختبار كما ترجعها الجودة
     */
    private function applyBatch(ExamCommittee $committee, array $batch): void
    {
        if (!empty($batch['id'])) {
            $committee->standard_committee_id = $batch['id'];
        }

        if (!empty($batch['status']) && $batch['status'] !== $committee->quality_status) {
            $committee->quality_status = $batch['status'];
            $committee->status = $batch['status_label'] ?? $batch['status'];
            $committee->status_updated_at = now();
        }

        if (!empty($batch['exam_datetime'])) {
            $committee->exam_datetime = $batch['exam_datetime'];
        }
    }

    // ========================
    // Helpers
    // ========================

    /**
     * رقم هوية محفّظ الطالب: محفّظ حلقته الفعّالة
     */
    private function teacherIdentity(StudentSubject $studentSubject): ?string
    {
        $halaqaId = HalaqaStudent::withoutGlobalScopes()
            ->where('student_id', $studentSubject->student_id)
            ->whereNull('to_date')
            ->whereNull('deleted_at')
            ->latest('id')
            ->value('halaqa_id');

        $userId = $halaqaId
            ? UserScope::where('scope_type', 'halaqa')->where('scope_id', $halaqaId)->active()->value('user_id')
            : null;

        return $userId
            ? User::withoutGlobalScope(GenderVisibilityScope::class)->whereKey($userId)->value('identity')
            : null;
    }

    private function request(string $method, string $path, ?int $committeeId = null, ?array $payload = null): Response
    {
        $config = config('thirdParty.quality_api');

        if (blank($config['base_url']) || blank($config['api_key'])) {
            throw new \RuntimeException('ربط الجودة غير مُعد (QUALITY_API_URL / QUALITY_API_KEY)');
        }

        $url = rtrim($config['base_url'], '/') . '/' . $path;

        try {
            $client = Http::withHeaders(['X-Api-Key' => $config['api_key']])
                ->acceptJson()
                ->timeout(30);

            // الجودة تتجاهل جسم الطلب بصيغة JSON وتقرأ صيغة form (والمصفوفات تُرسل students[0][id_num])
            $response = $method === 'get'
                ? $client->get($url)
                : $client->asForm()->post($url, $payload ?? []);
        } catch (\Throwable $e) {
            $this->log($method === 'get' ? QualitySyncLog::INCOMING : QualitySyncLog::OUTGOING, $committeeId, $payload, error: $e->getMessage());
            throw new \RuntimeException('تعذّر الاتصال ببرنامج الجودة: ' . $e->getMessage(), 0, $e);
        }

        // حالة الاستقبال لا تُسجَّل في السجل (لا تخص لجنة)
        if ($committeeId) {
            $this->log($method === 'get' ? QualitySyncLog::INCOMING : QualitySyncLog::OUTGOING, $committeeId, $payload,
                $response->json(), $response->status(), $response->successful() ? null : mb_substr($response->body(), 0, 2000));
        }

        return $response;
    }

    private function ensureSuccessful(Response $response): void
    {
        if ($response->status() === 401) {
            throw new \RuntimeException('مفتاح الجودة (QUALITY_API_KEY) غير صحيح');
        }

        if (!$response->successful() || $response->json('status') !== 'success') {
            throw new \RuntimeException('استجابة غير متوقعة من برنامج الجودة: ' . ($response->json('message') ?? $response->status()));
        }
    }

    /**
     * رسائل رفض الدفعة (422): أخطاء التحقق، أو نتائج الطلاب غير الصالحين
     */
    private function rejectionMessages(Response $response): array
    {
        $details = collect($response->json('errors') ?? [])->flatten()
            ->merge(collect($response->json('results') ?? [])
                ->filter(fn($result) => empty($result['valid']))
                ->map(fn($result) => "{$result['id_num']}: {$result['error']}"))
            ->unique()
            ->values();

        // معالج الأخطاء في المشروع يعرض أول رسالة فقط، فتُجمع التفاصيل في رسالة واحدة
        $message = 'رفض برنامج الجودة الدفعة: ' . ($response->json('message') ?? '');
        if ($details->count() > 1 || ($details->count() === 1 && $details[0] !== $response->json('message'))) {
            $message .= ' — ' . $details->implode(' • ');
        }

        return ['quality' => $message];
    }

    public function log(string $direction, ?int $committeeId, ?array $payload, ?array $response = null, ?int $status = null, ?string $error = null): void
    {
        QualitySyncLog::create([
            'direction' => $direction,
            'exam_committee_id' => $committeeId,
            'payload' => $payload,
            'response' => $response,
            'status_code' => $status,
            'error' => $error,
        ]);
    }
}
