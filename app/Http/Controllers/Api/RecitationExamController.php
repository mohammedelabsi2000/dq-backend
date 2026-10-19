<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Exam\RecitationExamResource;
use App\Models\RecitationExam;
use App\Services\ExamService;
use App\Services\SubjectProgressService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * السرد التجريبي: المرحلة التي ينتقل إليها الطالب بعد إكمال حفظ المادة.
 */
class RecitationExamController extends Controller
{
    public function __construct(
        protected ExamService $exams,
        protected SubjectProgressService $subjectProgress
    ) {}

    public function index(Request $request)
    {
        $query = RecitationExam::query();

        foreach (['student_id', 'subject_id', 'student_subject_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => [],
        ]);

        $exams = $query->with(['student', 'subject'])->get();

        return $this->successWithPagination(
            RecitationExamResource::collection($exams),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    /**
     * تسجيل نتيجة السرد التجريبي. رقم المحاولة والمسموح به من الأخطاء والتنبيهات تُحدَّد تلقائياً.
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

        $exam = $this->exams->recordRecitation(
            $resolved['student_subject'],
            collect($data)->only(['errors_count', 'alerts_count', 'exam_date', 'notes'])->all()
        );

        return $this->success(
            $this->payload($exam),
            'تم تسجيل السرد التجريبي بنجاح',
            201
        );
    }

    public function show(RecitationExam $recitationExam)
    {
        return $this->success(
            $this->payload($recitationExam),
            'تفاصيل السرد التجريبي',
            200
        );
    }

    public function update(Request $request, RecitationExam $recitationExam)
    {
        $data = $request->validate($this->resultRules(), $this->messages());

        $exam = $this->exams->updateRecitation($recitationExam, $data);

        return $this->success(
            $this->payload($exam),
            'تم تحديث السرد التجريبي بنجاح',
            200
        );
    }

    public function destroy(RecitationExam $recitationExam)
    {
        $this->exams->deleteRecitation($recitationExam);

        return $this->success(
            null,
            'تم حذف السرد التجريبي بنجاح',
            200
        );
    }

    private function resultRules(): array
    {
        return [
            'errors_count' => 'nullable|integer|min:0|required_with:alerts_count',
            'alerts_count' => 'nullable|integer|min:0|required_with:errors_count',
            'exam_date' => 'nullable|date|before_or_equal:' . now('Asia/Gaza')->toDateString(),
            'notes' => 'nullable|string',
        ];
    }

    private function messages(): array
    {
        return [
            'errors_count.required_with' => 'عدد الأخطاء مطلوب مع عدد التنبيهات',
            'alerts_count.required_with' => 'عدد التنبيهات مطلوب مع عدد الأخطاء',
            'exam_date.before_or_equal' => 'لا يمكن إدخال تاريخ مستقبلي',
        ];
    }

    private function payload(RecitationExam $exam): array
    {
        $exam->load(['student', 'subject', 'studentSubject.subject']);

        return (new RecitationExamResource($exam))->resolve() + [
            // مرحلة الطالب في المادة بعد هذه النتيجة: trial_recitation | trial_exam | final_exam | completed
            'exam_stage' => $exam->studentSubject ? $this->exams->stage($exam->studentSubject) : null,
        ];
    }
}
