<?php

namespace App\Http\Controllers\Api;

use App\Enums\HalaqaReferenceType;
use App\Filters\HalaqaStudentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\HalaqaStudent\StoreHalaqaStudentRequest;
use App\Http\Requests\HalaqaStudent\UpdateHalaqaStudentRequest;
use App\Http\Resources\HalaqaStudentResource;
use App\Models\HalaqaStudent;
use App\Services\HalaqaStudentService;
use Illuminate\Http\Request;

class HalaqaStudentController extends Controller
{
    protected HalaqaStudentService $halaqaStudentService;

    public function __construct(HalaqaStudentService $halaqaStudentService)
    {
        $this->halaqaStudentService = $halaqaStudentService;
    }

    /**
     * عرض جميع التسجيلات
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', HalaqaStudent::class);
        $query = HalaqaStudent::query()->visibleTo(auth()->user());
        $query->whereHas('student');

        // Filter by branch (through halaqa)
        if (request()->filled('branch_id')) {
            $branchId = request()->integer('branch_id');
            $query->whereHas('halaqa', function ($q) use ($branchId) {
                $q->where(function ($hq) use ($branchId) {
                    // Halaqas under centers in this branch
                    $hq->whereHasMorph('reference', ['center'], function ($centerQuery) use ($branchId) {
                        $centerQuery->whereHas('region', function ($regionQuery) use ($branchId) {
                            $regionQuery->where('branch_id', $branchId);
                        });
                    })
                        // Halaqas directly under regions in this branch
                        ->orWhereHasMorph('reference', ['region'], function ($regionQuery) use ($branchId) {
                            $regionQuery->where('branch_id', $branchId);
                        });
                });
            });
        }

        // Filter by center (through halaqa)
        if (request()->filled('center_id')) {
            $query->whereHas('halaqa', function ($q) {
                $q->whereHasMorph('reference', [HalaqaReferenceType::Center->code()], function ($centerQuery) {
                    $centerQuery->where('id', request()->integer('center_id'));
                });
            });
        }

        // Filter by region (through halaqa)
        if (request()->filled('region_id')) {
            $regionId = request()->integer('region_id');
            $query->whereHas('halaqa', function ($q) use ($regionId) {
                $q->where(function ($hq) use ($regionId) {
                    // Halaqas directly under this region
                    $hq->whereHasMorph('reference', [HalaqaReferenceType::Region->code()], function ($regionQuery) use ($regionId) {
                        $regionQuery->where('id', $regionId);
                    })
                        // Halaqas under centers in this region
                        ->orWhereHasMorph('reference', [HalaqaReferenceType::Center->code()], function ($centerQuery) use ($regionId) {
                            $centerQuery->where('region_id', $regionId);
                        });
                });
            });
        }

        // Filter by specific halaqa
        if (request()->filled('halaqa_id')) {
            $query->where('halaqa_id', request()->integer('halaqa_id'));
        }

        $filterdQuery = (new HalaqaStudentFilter($query, $request))->apply();
        $q = $this->applyFilters($filterdQuery, [
            'searchColumns' => ['id', 'student_id', 'halaqa_id'],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc'
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $data = $query->with([
            'halaqa',
            'halaqa.reference',
            'student',
            'enrollment_status'
        ])->get();

        return $this->successWithPagination(
            HalaqaStudentResource::collection($data),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * عرض تسجيل محدد
     */
    public function show(HalaqaStudent $halaqaStudent)
    {
        $this->authorize('view', $halaqaStudent);
        $halaqaStudent = $halaqaStudent->load([
            'halaqa',
            'halaqa.reference',
            'student',
            'enrollment_status',
        ]);

        return $this->success(
            new HalaqaStudentResource($halaqaStudent),
            'success',
            200
        );
    }

    /**
     * إنشاء تسجيل جديد
     */
    public function store(StoreHalaqaStudentRequest $request)
    {
        $validated = $request->validated();

        $studentsCreated = [];
        $studentsAlreadyEnrolled = [];

        foreach ($validated['students'] as $studentId) {
            // التحقق هل الطالب مسجل في حلقة نشطة
            if ($this->halaqaStudentService->isStudentEnrolledInActiveHalaqa($studentId)) {
                $studentsAlreadyEnrolled[] = $studentId;
                continue;
            }

            $student = HalaqaStudent::create([
                'halaqa_id' => $validated['halaqa_id'],
                'student_id' => $studentId,
                'from_date' => $validated['from_date'],
                'enrollment_status_id' => $validated['enrollment_status_id'],
            ]);

            $studentsCreated[] = $student;
        }

        return $this->success(
            HalaqaStudentResource::collection(
                HalaqaStudent::with(['halaqa', 'student', 'enrollment_status'])
                    ->whereIn('id', collect($studentsCreated)->pluck('id'))
                    ->get()
            ),
            'تم تسجيل الطلاب في الحلقة بنجاح'
        );
    }

    /**
     * تعديل تسجيل موجود
     */
    public function update(
        UpdateHalaqaStudentRequest $request
    ) {
        $validated = $request->validated();

        HalaqaStudent::whereIn('student_id', $validated['students'])
            ->whereNull('to_date')
            ->update([
                'from_date' => $validated['from_date'],
                'enrollment_status_id' => $validated['enrollment_status_id'],
            ]);

        return $this->success(
            null,
            'تم تحديث بيانات التسجيل بنجاح',
        );
    }

    /**
     * حذف تسجيل
     */
    public function destroy(HalaqaStudent $halaqaStudent)
    {
        $this->authorize('delete', $halaqaStudent);
        $halaqaStudent->delete();

        return $this->success(
            null,
            'تم حذف التسجيل بنجاح',
            202
        );
    }
}
