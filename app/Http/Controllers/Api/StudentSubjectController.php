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
        $studentSubject = StudentSubject::create(
            $request->validated()
        );

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
            $request->validated()
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
