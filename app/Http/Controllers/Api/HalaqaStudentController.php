<?php

namespace App\Http\Controllers\Api;

use App\Enums\HalaqaReferenceType;
use App\Filters\HalaqaStudentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\HalaqaStudent\StoreHalaqaStudentRequest;
use App\Http\Requests\HalaqaStudent\UpdateHalaqaStudentRequest;
use App\Http\Resources\HalaqaStudentResource;
use App\Models\HalaqaStudent;
use Illuminate\Http\Request;

class HalaqaStudentController extends Controller
{

    /**
     * عرض جميع التسجيلات
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', HalaqaStudent::class);
        $query = HalaqaStudent::query()->visibleTo(auth()->user());

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

        // Filter by active students (no to_date or to_date in future)
        /* if (request()->boolean('active_only')) {
            $query->where(function ($q) {
                $q->whereNull('to_date')
                    ->orWhere('to_date', '>=', now());
            });
        } */

        // Filter by student
        /* if (request()->filled('student_id')) {
            $query->where('student_id', request()->integer('student_id'));
        } */

        /* $search = request()->get('search');

        $query = $query->dqSearch($search, [], [
            // 'halaqa' => ['name'],
            'student' => ['full_name'],
        ]); */
        /* $query = $query->whereHas('student', function ($qr) use ($search) {
            foreach (['fName'] as $column) {
                $qr->where($column, 'LIKE', "%{$search}%");
            }
        }); */

        $filterdQuery = (new HalaqaStudentFilter($query, $request))->apply();
        $q = $this->applyFilters($filterdQuery, [
            'searchColumns' => ['id', 'student_id', 'halaqa_id'],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc'
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // حساب العدد الحقيقي بعد الفلترة
        // $total = (clone $query)->count();


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

            // التحقق هل الطالب مسجل في حلقة أخرى
            $existingEnrollment = HalaqaStudent::where('student_id', $studentId)
                ->whereNull('to_date')
                ->exists();

            if ($existingEnrollment) {
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
        UpdateHalaqaStudentRequest $request,
        HalaqaStudent $halaqaStudent
    ) {
        $validated = $request->validated();

        if ($halaqaStudent->halaqa_id != $validated['halaqa_id'] || $halaqaStudent->from_date != $validated['from_date']) {
            // اغلاق كل السجلات المفتوحة
            HalaqaStudent::where('student_id', $halaqaStudent->student_id)
                ->whereNull('to_date')
                ->update(['to_date' => $validated['from_date']]);

            $newRecord = HalaqaStudent::create([
                'student_id' => $halaqaStudent->student_id,
                'halaqa_id' => $validated['halaqa_id'],
                'from_date' => $validated['from_date'],
                'enrollment_status_id' => $validated['enrollment_status_id'] ?? $halaqaStudent->enrollment_status_id,
            ]);

            return $this->success(
                new HalaqaStudentResource($newRecord->load(['halaqa', 'student', 'enrollment_status'])),
                'تم نقل الطالب إلى الحلقة الجديدة بنجاح',
                201
            );
        }

        $halaqaStudent->update($validated);

        return $this->success(
            new HalaqaStudentResource($halaqaStudent->load(['halaqa', 'student', 'enrollment_status'])),
            'تم تحديث بيانات التسجيل بنجاح',
            200
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
