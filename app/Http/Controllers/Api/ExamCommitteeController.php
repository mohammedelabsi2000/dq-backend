<?php

namespace App\Http\Controllers\Api;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Http\Resources\Exam\ExamCommitteeResource;
use App\Http\Resources\Exam\FinalExamCandidateResource;
use App\Models\ExamCommittee;
use App\Models\ExamCommitteeStudent;
use App\Models\Region;
use App\Models\StudentSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * لجان الاختبارات النهائية: تشكيل اللجان وتنسيب الطلاب المرشحين (من اجتازوا الاختبار التجريبي) لها.
 */
class ExamCommitteeController extends Controller
{
    private const CANDIDATE_RELATIONS = [
        'student.mosque.region.branch',
        'subject',
        'level.plan',
        'latestTrialExam',
        'examCommitteeStudent.committee',
    ];

    // ========================
    // اللجان
    // ========================

    public function index(Request $request)
    {
        $query = ExamCommittee::query()->withCount('committeeStudents');

        foreach (['branch_id', 'region_id', 'subject_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('gender')) {
            $query->where('gender', $this->gender($request->input('gender')));
        }

        if ($request->filled('is_multi_subject')) {
            $query->where('is_multi_subject', $request->boolean('is_multi_subject'));
        }

        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => ['name', 'exam_location'],
            'orderBy' => $request->input('order_by', 'desc'),
        ]);

        $committees = $query->with(['subject', 'branch', 'region'])->get();

        return $this->successWithPagination(
            ExamCommitteeResource::collection($committees),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    public function store(Request $request)
    {
        $committee = ExamCommittee::create($this->validated($request));

        return $this->success(
            $this->payload($committee),
            'تم تشكيل اللجنة بنجاح',
            201
        );
    }

    public function show(ExamCommittee $committee)
    {
        return $this->success(
            $this->payload($committee),
            'تفاصيل اللجنة',
            200
        );
    }

    public function update(Request $request, ExamCommittee $committee)
    {
        $data = $this->validated($request, $committee);

        // تغيير الفئة أو المساق قد يجعل الطلاب المنسَّبين غير مطابقين للجنة
        $constraintsChanged = $data['gender'] !== $committee->gender->value
            || ($data['subject_id'] !== null && (int) $data['subject_id'] !== (int) $committee->subject_id);

        if ($constraintsChanged && $committee->committeeStudents()->exists()) {
            throw ValidationException::withMessages([
                'gender' => 'لا يمكن تغيير فئة اللجنة أو مساقها بعد تنسيب طلاب لها؛ أزل الطلاب أولاً',
            ]);
        }

        $committee->update($data);

        return $this->success(
            $this->payload($committee),
            'تم تحديث اللجنة بنجاح',
            200
        );
    }

    public function destroy(ExamCommittee $committee)
    {
        if ($committee->committeeStudents()->exists()) {
            return $this->error('لا يمكن حذف لجنة منسَّب لها طلاب؛ أزل الطلاب أولاً', 422);
        }

        $committee->delete();

        return $this->success(
            null,
            'تم حذف اللجنة بنجاح',
            200
        );
    }

    // ========================
    // المرشحون للاختبارات النهائية
    // ========================

    /**
     * الطلاب المرشحون للاختبارات النهائية (اجتازوا الاختبار التجريبي ولم يُنسَّبوا للجنة بعد)،
     * مع الفلترة بالفرع والمحلية والمواد والفئة.
     */
    public function candidates(Request $request)
    {
        return $this->candidatesResponse($request, StudentSubject::finalExamCandidates());
    }

    /**
     * المرشحون المناسبون للجنة معيّنة: فئتها (طلاب/طالبات) ومساقها إن كانت لمساق واحد
     */
    public function committeeCandidates(Request $request, ExamCommittee $committee)
    {
        $query = StudentSubject::finalExamCandidates()
            ->whereHas('student', fn($q) => $q->where('gender', $committee->gender->value));

        if (!$committee->is_multi_subject) {
            $query->where('subject_id', $committee->subject_id);
        }

        return $this->candidatesResponse($request, $query);
    }

    private function candidatesResponse(Request $request, $query)
    {
        $request->validate([
            'branch_id' => 'nullable|integer',
            'region_id' => 'nullable|integer',
            'subject_id' => 'nullable',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'integer',
            'gender' => ['nullable', Rule::in(['male', 'female', Gender::Male->value, Gender::Female->value])],
            'search' => 'nullable|string',
        ]);

        // الفرع والمحلية يؤخذان من مسجد الطالب
        if ($request->filled('branch_id')) {
            $query->whereHas('student.mosque.region', fn($q) => $q->where('branch_id', $request->input('branch_id')));
        }

        if ($request->filled('region_id')) {
            $query->whereHas('student.mosque', fn($q) => $q->where('region_id', $request->input('region_id')));
        }

        // المواد: subject_id (رقم أو أرقام مفصولة بفاصلة) أو subject_ids[]
        $subjectIds = collect($request->input('subject_ids', []))
            ->merge(is_array($request->input('subject_id')) ? $request->input('subject_id') : explode(',', (string) $request->input('subject_id')))
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique();

        if ($subjectIds->isNotEmpty()) {
            $query->whereIn('subject_id', $subjectIds);
        }

        if ($request->filled('gender')) {
            $query->whereHas('student', fn($q) => $q->where('gender', $this->gender($request->input('gender'))));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', fn($q) => $q->where('full_name', 'LIKE', "%{$search}%")->orWhere('identity', 'LIKE', "%{$search}%"));
        }

        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, ['search' => '', 'orderBy' => 'asc', 'orderColumn' => 'student_subjects.id']);

        return $this->successWithPagination(
            FinalExamCandidateResource::collection($query->with(self::CANDIDATE_RELATIONS)->get()),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    // ========================
    // طلاب اللجنة
    // ========================

    public function students(ExamCommittee $committee)
    {
        $query = StudentSubject::whereHas('student')
            ->whereHas('examCommitteeStudent', fn($q) => $q->where('exam_committee_id', $committee->id));

        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, ['search' => '', 'orderBy' => 'asc', 'orderColumn' => 'student_subjects.id']);

        return $this->successWithPagination(
            FinalExamCandidateResource::collection($query->with(self::CANDIDATE_RELATIONS)->get()),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    /**
     * تنسيب مرشحين للجنة. كل الطلاب يجب أن يكونوا مرشحين ومطابقين لفئة اللجنة ومساقها، وإلا لا يُنسَّب أحد.
     */
    public function assignStudents(Request $request, ExamCommittee $committee)
    {
        $data = $request->validate([
            'student_subject_ids' => 'required|array|min:1',
            'student_subject_ids.*' => 'integer|distinct',
        ], [
            'student_subject_ids.required' => 'حدّد الطلاب المراد تنسيبهم',
        ]);

        $ids = collect($data['student_subject_ids'])->map(fn($id) => (int) $id);
        $studentSubjects = StudentSubject::with(['student', 'subject', 'examCommitteeStudent.committee'])->findMany($ids)->keyBy('id');
        $candidateIds = StudentSubject::finalExamCandidates(false)->whereIn('id', $ids)->pluck('id');
        $errors = [];

        foreach ($ids as $index => $id) {
            $studentSubject = $studentSubjects->get($id);
            $name = $studentSubject?->student?->full_name ?? "رقم {$id}";

            $error = match (true) {
                !$studentSubject || !$studentSubject->student => "سجل المادة رقم {$id} غير موجود",
                (bool) $studentSubject->examCommitteeStudent => "الطالب {$name} منسَّب مسبقاً للجنة \"{$studentSubject->examCommitteeStudent->committee?->name}\"",
                !$candidateIds->contains($id) => "الطالب {$name} ليس مرشحاً للاختبار النهائي في هذا المساق (لم يجتز الاختبار التجريبي)",
                $studentSubject->student->gender !== $committee->gender => "الطالب {$name} لا يطابق فئة اللجنة",
                !$committee->is_multi_subject && (int) $studentSubject->subject_id !== (int) $committee->subject_id => "مساق الطالب {$name} يختلف عن مساق اللجنة",
                default => null,
            };

            if ($error) {
                $errors["student_subject_ids.{$index}"] = $error;
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($studentSubjects, $committee) {
            foreach ($studentSubjects as $studentSubject) {
                ExamCommitteeStudent::create([
                    'exam_committee_id' => $committee->id,
                    'student_subject_id' => $studentSubject->id,
                    'student_id' => $studentSubject->student_id,
                    'subject_id' => $studentSubject->subject_id,
                ]);
            }
        });

        return $this->success(
            $this->payload($committee),
            'تم تنسيب الطلاب للجنة بنجاح',
            201
        );
    }

    public function removeStudent(ExamCommittee $committee, StudentSubject $studentSubject)
    {
        $assignment = $committee->committeeStudents()->where('student_subject_id', $studentSubject->id)->first();

        if (!$assignment) {
            return $this->error('الطالب غير منسَّب لهذه اللجنة', 404);
        }

        $assignment->delete();

        return $this->success(
            null,
            'تمت إزالة الطالب من اللجنة بنجاح',
            200
        );
    }

    // ========================
    // Helpers
    // ========================

    /**
     * التحقق من بيانات اللجنة. عند التعديل تُدمج القيم المُرسلة مع القيم الحالية.
     */
    private function validated(Request $request, ?ExamCommittee $committee = null): array
    {
        $presence = $committee ? 'sometimes' : 'required';

        $input = $request->validate([
            'name' => [$presence, 'string', 'max:255'],
            'is_multi_subject' => [$presence, 'boolean'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'start_date' => [$presence, 'date'],
            'gender' => [$presence, Rule::in(['male', 'female', Gender::Male->value, Gender::Female->value])],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'proposed_exam_date' => ['nullable', 'date'],
            'exam_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'اسم اللجنة مطلوب',
            'is_multi_subject.required' => 'حدّد هل اللجنة لعدة مساقات أم لمساق واحد',
            'start_date.required' => 'تاريخ البدء مطلوب',
            'gender.required' => 'فئة اللجنة (طلاب أم طالبات) مطلوبة',
            'gender.in' => 'فئة اللجنة يجب أن تكون طلاب (ذكر) أو طالبات (أنثى)',
            'subject_id.exists' => 'المساق المحدد غير موجود',
            'branch_id.exists' => 'الفرع المحدد غير موجود',
            'region_id.exists' => 'المحلية المحددة غير موجودة',
        ]);

        $data = array_merge(
            $committee ? $committee->only(['name', 'is_multi_subject', 'subject_id', 'start_date', 'branch_id', 'region_id', 'proposed_exam_date', 'exam_location', 'notes']) : [],
            $input
        );

        $data['gender'] = isset($input['gender']) ? $this->gender($input['gender']) : $committee->gender->value;
        $data['is_multi_subject'] = (bool) $data['is_multi_subject'];
        $data['subject_id'] ??= null;
        $errors = [];

        // لجنة المساق الواحد يُحدَّد لها مساق، ولجنة عدة مساقات بلا مساق محدد
        if ($data['is_multi_subject']) {
            $data['subject_id'] = null;
        } elseif (!$data['subject_id']) {
            $errors['subject_id'] = 'المساق مطلوب للجنة المساق الواحد';
        }

        if (!empty($data['region_id']) && !empty($data['branch_id'])
            && (int) Region::whereKey($data['region_id'])->value('branch_id') !== (int) $data['branch_id']) {
            $errors['region_id'] = 'المحلية المحددة لا تتبع الفرع المحدد';
        }

        if (!empty($data['proposed_exam_date']) && strtotime((string) $data['proposed_exam_date']) < strtotime(date('Y-m-d', strtotime((string) $data['start_date'])))) {
            $errors['proposed_exam_date'] = 'موعد الاختبار المقترح يجب ألا يسبق تاريخ البدء';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    private function gender(string $value): string
    {
        return match ($value) {
            'male', Gender::Male->value => Gender::Male->value,
            default => Gender::Female->value,
        };
    }

    private function payload(ExamCommittee $committee): ExamCommitteeResource
    {
        return new ExamCommitteeResource(
            $committee->fresh()->load(['subject', 'branch', 'region'])->loadCount('committeeStudents')
        );
    }
}
