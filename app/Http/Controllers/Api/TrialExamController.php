<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Exam\TrialExamResource;
use App\Models\TrialExam;
use App\Services\ExamService;
use App\Services\SubjectProgressService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * الاختبار التجريبي: يكون بعد النجاح في السرد التجريبي، والنجاح فيه ينقل الطالب لمرحلة الاختبار النهائي.
 */
class TrialExamController extends Controller
{
    public function __construct(
        protected ExamService $exams,
        protected SubjectProgressService $subjectProgress
    ) {}

    public function index(Request $request)
    {
        $query = TrialExam::query();

        foreach (['student_id', 'subject_id', 'student_subject_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => [],
            // 'orderColumn' => 'created_at',
        ]);

        $exams = $query->with(['student', 'subject', 'lastRecitationExam'])->get();

        return $this->successWithPagination(
            TrialExamResource::collection($exams),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    /**
     * تسجيل نتيجة الاختبار التجريبي. رقم المحاولة وعلامة النجاح تُحدَّدان تلقائياً.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            // يلزم فقط لو كانت المادة للطالب في أكثر من خطة
            'student_subject_id' => 'nullable|integer',
            ...$this->resultRules(),
        ], $this->messages());

        $resolved = $this->subjectProgress->resolveStudentSubject(
            (int) $data['student_id'],
            (int) $data['subject_id'],
            isset($data['student_subject_id']) ? (int) $data['student_subject_id'] : null
        );

        if (isset($resolved['error'])) {
            throw ValidationException::withMessages([$resolved['field'] => $resolved['error']]);
        }

        $exam = $this->exams->recordTrial(
            $resolved['student_subject'],
            collect($data)->only(['mark', 'exam_date', 'notes'])->all()
        );

        return $this->success(
            $this->payload($exam),
            'تم تسجيل الاختبار التجريبي بنجاح',
            201
        );
    }

    public function show(TrialExam $trialExam)
    {
        return $this->success(
            $this->payload($trialExam),
            'تفاصيل الاختبار التجريبي',
            200
        );
    }

    public function update(Request $request, TrialExam $trialExam)
    {
        $data = $request->validate($this->resultRules(), $this->messages());

        $exam = $this->exams->updateTrial($trialExam, $data);

        return $this->success(
            $this->payload($exam),
            'تم تحديث الاختبار التجريبي بنجاح',
            200
        );
    }

    public function destroy(TrialExam $trialExam)
    {
        $this->exams->deleteTrial($trialExam);

        return $this->success(
            null,
            'تم حذف الاختبار التجريبي بنجاح',
            200
        );
    }

    private function resultRules(): array
    {
        return [
            'mark' => 'nullable|numeric|min:0|max:100',
            'exam_date' => 'nullable|date|before_or_equal:' . now('Asia/Gaza')->toDateString(),
            'notes' => 'nullable|string',
        ];
    }

    private function messages(): array
    {
        return [
            'exam_date.before_or_equal' => 'لا يمكن إدخال تاريخ مستقبلي',
        ];
    }

    private function payload(TrialExam $exam): array
    {
        $exam->load(['student', 'subject', 'lastRecitationExam', 'studentSubject.subject']);

        return (new TrialExamResource($exam))->resolve() + [
            // مرحلة الطالب في المادة بعد هذه النتيجة: trial_exam | final_exam | completed
            'exam_stage' => $exam->studentSubject ? $this->exams->stage($exam->studentSubject) : null,
        ];
    }
}
