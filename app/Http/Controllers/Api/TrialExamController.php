<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Exam\TrialExamResource;
use App\Models\StudentSubject;
use App\Models\TrialExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrialExamController extends Controller
{
    public function index()
    {
        $query = TrialExam::query();

        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => [],
            // 'orderColumn' => 'created_at',
        ]);

        $exams = $query->with(['student', 'subject'])->get();

        return $this->successWithPagination(
            TrialExamResource::collection($exams),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'mark' => 'nullable|numeric|min:0|max:100',
            'success_mark' => 'nullable|numeric|min:0|max:100',
            'attempt_number' => 'required|integer|min:1',
            'exam_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Create a new TrialExam record
        $TrialExam = TrialExam::create($validatedData);

        return $this->success(
            $TrialExam,
            'تم إنشاء الاختبار التجريبي بنجاح',
            201
        );
    }

    public function show(TrialExam $TrialExam)
    {
        return $this->success(
            $TrialExam->load(['student', 'subject']),
            'تفاصيل الاختبار التجريبي',
            200
        );
    }
    public function update(Request $request, TrialExam $TrialExam)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'student_id' => 'sometimes|required|exists:students,id',
            'subject_id' => 'sometimes|required|exists:subjects,id',
            'mark' => 'nullable|numeric|min:0|max:100',
            'success_mark' => 'nullable|numeric|min:0|max:100',
            'attempt_number' => 'sometimes|required|integer|min:1',
            'exam_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Update the TrialExam record
        $TrialExam->update($validatedData);

        return $this->success(
            $TrialExam,
            'تم تحديث الاختبار التجريبي بنجاح',
            200
        );
    }

    public function destroy(TrialExam $TrialExam)
    {
        $TrialExam->delete();

        return $this->success(
            null,
            'تم حذف الاختبار التجريبي بنجاح',
            200
        );
    }
}
