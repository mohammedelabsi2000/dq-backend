<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Exam\ExamRowResource;
use App\Models\RecitationExam;
use App\Models\TrialExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * قائمة موحّدة للسرد التجريبي والاختبار التجريبي لعرضهما في واجهة واحدة.
 * تعرض افتراضياً ما حالته pending فقط (بانتظار إدخال النتيجة).
 * الإدخال والتعديل يتمان عبر recitation-exams و trial-exams حسب نوع الصف.
 */
class ExamController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'nullable|in:' . ExamRowResource::TYPE_RECITATION . ',' . ExamRowResource::TYPE_EXAM,
            // الافتراضي pending: القائمة تعرض ما ينتظر إدخال نتيجته فقط
            'status' => 'nullable|in:pending,passed,failed,all',
            'skip' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        $sources = collect([
            ExamRowResource::TYPE_RECITATION => $this->rows('recitation_exams', ExamRowResource::TYPE_RECITATION, $request, [
                'pending' => 'errors_count is null',
                'passed' => 'errors_count <= errors_count_allowed and alerts_count <= alerts_count_allowed',
                'failed' => 'errors_count is not null and not (errors_count <= errors_count_allowed and alerts_count <= alerts_count_allowed)',
            ]),
            ExamRowResource::TYPE_EXAM => $this->rows('trial_exams', ExamRowResource::TYPE_EXAM, $request, [
                'pending' => 'mark is null',
                'passed' => 'mark >= success_mark',
                'failed' => 'mark < success_mark',
            ]),
        ])->only($request->input('type') ?: [ExamRowResource::TYPE_RECITATION, ExamRowResource::TYPE_EXAM])->values();

        $union = $sources->skip(1)->reduce(fn($query, $other) => $query->unionAll($other), $sources->first());

        $skip = (int) $request->input('skip', 0);
        $limit = (int) $request->input('limit', 10);
        $total = DB::query()->fromSub($union, 'exams')->count();

        // الأحدث أولاً
        $page = DB::query()->fromSub($union, 'exams')
            ->orderByDesc('created_at')
            ->orderByDesc('type')
            ->orderByDesc('id')
            ->skip($skip)
            ->take($limit)
            ->get();

        $models = [
            ExamRowResource::TYPE_RECITATION => RecitationExam::with(['student', 'subject'])
                ->findMany($page->where('type', ExamRowResource::TYPE_RECITATION)->pluck('id'))
                ->keyBy('id'),
            ExamRowResource::TYPE_EXAM => TrialExam::with(['student', 'subject'])
                ->findMany($page->where('type', ExamRowResource::TYPE_EXAM)->pluck('id'))
                ->keyBy('id'),
        ];

        $exams = $page->map(fn($row) => $models[$row->type]->get($row->id))->filter()->values();

        return $this->successWithPagination(
            ExamRowResource::collection($exams),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    /**
     * صفوف جدول واحد (المعرّف والنوع وتاريخ الإنشاء) بعد تطبيق الفلاتر
     */
    private function rows(string $table, string $type, Request $request, array $statusConditions)
    {
        $query = DB::table($table)
            ->selectRaw("id, '{$type}' as type, created_at")
            ->whereNull('deleted_at');

        foreach (['student_id', 'subject_id', 'student_subject_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $status = $request->input('status') ?: 'pending';

        if ($status !== 'all') {
            $query->whereRaw($statusConditions[$status]);
        }

        return $query;
    }
}
