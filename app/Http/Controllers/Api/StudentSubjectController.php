<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentSubjec\StudentSubjectRequest;
use App\Http\Resources\StudentSubjectResource;
use App\Models\StudentSubject;
use Illuminate\Http\Request;

class StudentSubjectController extends Controller
{
    public function index()
    {
        $studentSubjects = StudentSubject::query()
            ->with([
                'student',
                'subject',
                'level',
                'resultStatus',
                'teacher',
            ])
            ->latest()
            ->paginate();

        return $this->success(
            StudentSubjectResource::collection($studentSubjects)
        );
    }

    public function store(StudentSubjectRequest $request)
    {
        $data = $request->safe()->except('on_previous_pass');
        $decision = $request->input('on_previous_pass');

        // الطالب نجح في هذه المادة سابقاً: يُنبَّه المستخدم ليقرر الإعادة أو الإعفاء
        $previousPass = StudentSubject::previousPass($data['student_id'], $data['subject_id']);

        if ($previousPass && !$decision) {
            return $this->error(
                'الطالب أنجز هذا المساق سابقاً. حدّد القرار في الحقل on_previous_pass: إعادة (retake) أو إعفاء (exempt)',
                409,
                null,
                [
                    'requires_decision' => true,
                    'options' => ['retake', 'exempt'],
                    'previous_student_subject' => new StudentSubjectResource($previousPass),
                    'previous_plan' => $previousPass->level?->plan?->only(['id', 'name']),
                ]
            );
        }

        // إعفاء (معادلة): يُسجَّل ناجحاً مباشرة مع نسخ الدرجة من السجل السابق
        if ($previousPass && $decision === 'exempt') {
            $today = now('Asia/Gaza')->toDateString();
            $note = 'معادلة من مساق أنجزه الطالب سابقاً'
                . ($previousPass->level?->plan ? ' في خطة "' . $previousPass->level->plan->name . '"' : '')
                . " (سجل رقم {$previousPass->id})";

            $data = array_merge($data, [
                'result_status_id' => $previousPass->result_status_id,
                'grade' => $previousPass->grade,
                'grade_date' => $previousPass->grade_date?->toDateString() ?? $today,
                'from_date' => $data['from_date'] ?? $today,
                'to_date' => $data['to_date'] ?? $today,
                'notes' => trim(($data['notes'] ?? '') . "\n" . $note),
            ]);
        }

        $studentSubject = StudentSubject::create($data);

        $studentSubject->load([
            'student',
            'subject',
            'level',
            'resultStatus',
            'teacher',
        ]);

        return $this->success(
            new StudentSubjectResource($studentSubject),
            'تمت الإضافة بنجاح',
            201
        );
    }

    public function show(StudentSubject $studentSubject)
    {
        $studentSubject->load([
            'student',
            'subject',
            'level',
            'resultStatus',
            'teacher',
        ]);

        return $this->success(
            new StudentSubjectResource($studentSubject)
        );
    }
    public function update(StudentSubjectRequest $request, StudentSubject $studentSubject)
    {
        $studentSubject->update(
            $request->safe()->except('on_previous_pass')
        );

        $studentSubject->load([
            'student',
            'subject',
            'level',
            'resultStatus',
            'teacher',
        ]);

        return $this->success(
            new StudentSubjectResource($studentSubject)
        );
    }
    public function destroy(StudentSubject $studentSubject)
    {
        $studentSubject->delete();

        return $this->success(null, 'تم حذف مساق الطالب');
    }
}
