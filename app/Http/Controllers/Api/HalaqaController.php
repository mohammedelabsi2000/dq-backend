<?php

namespace App\Http\Controllers\Api;

use App\Enums\HalaqaReferenceType;
use App\Exports\HalaqaExport;
use App\Filters\HalaqaFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Halaqa\HalaqaRequest;
use App\Http\Resources\HalaqaResource;
use App\Models\Halaqa;
use App\Models\HalaqaStatus;
use App\Models\User;
use App\Models\UserScope;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HalaqaController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $this->authorize('viewAny', Halaqa::class);

        $query = Halaqa::query()->visibleTo(auth()->user())->where('is_approved', true);

        if (auth()->user()->hasPermissionTo('halaqas.restore')) {
            $query = $query->withTrashed();
        }

        $query = (new HalaqaFilter($query, $request))->apply();

        // Filter by specific center
        if ($request->filled('center_id')) {
            $query->whereHasMorph(
                'reference',
                [HalaqaReferenceType::Center->value],
                function ($query) {
                    $query->where('id', request()->integer('center_id'));
                }
            );
        }

        // Filter by specific region
        if ($request->filled('region_id')) {
            $regionId = $request->integer('region_id');
            $query->where(function ($q) use ($regionId) {
                // Halaqas directly under this region
                $q->whereHasMorph('reference', [HalaqaReferenceType::Region->value], function ($regionQuery) use ($regionId) {
                    $regionQuery->where('id', $regionId);
                })
                    // Halaqas under centers in this region
                    ->orWhereHasMorph('reference', [HalaqaReferenceType::Center->value], function ($centerQuery) use ($regionId) {
                        $centerQuery->where('region_id', $regionId);
                    });
            });
        }
        // Filter by reference type
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->input('reference_type'));
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->integer('type_id'));
        }

        if ($request->boolean('with_students')) {
            $query->with('students');
        }

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
        ]);

        $query = $q['query'];
        $total = $q['count'];
        $halaqas = $query->with(['reference', 'type', 'students', 'supervisors.user', 'lastStatus'])->get();

        return $this->successWithPagination(
            HalaqaResource::collection($halaqas),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param HalaqaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(HalaqaRequest $request)
    {
        // $halaqa = Halaqa::create($request->validated());
        $halaqaData = $request->validated();
        $halaqaStatusData = [
            'sponsorship_type_id' => $request->input('sponsorship_type_id'), // أو أي نوع كفالة افتراضي إذا كان موجودًا
            'sponsor_entity' => $request->input('sponsor_entity'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'notes' => $request->input('notes'),
        ];
        $teacherId = $halaqaData['teacher_id'] ?? null;

        // unset from_date and to_date from $halaqaData since they are not part of Halaqa model
        unset(
            // $halaqaData['from_date'],
            // $halaqaData['to_date'],
            $halaqaData['sponsorship_type_id'],
            $halaqaData['sponsor_entity'],
            $halaqaData['notes'],
            $halaqaData['teacher_id'],
        );
        $halaqa = Halaqa::create([
            ...$halaqaData,
        ]);

        if ($teacherId) {
            $this->assignTeacherToHalaqa(User::findOrFail($teacherId), $halaqa);
        }

        // إرسال طلب الاعتماد
        try {
            $approvalRequest = $halaqa->submitForApproval(auth()->user());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }

        $message = $approvalRequest === null
            ? 'تم إنشاء الحلقة وتفعيلها مباشرة'    // المدير العام
            : 'تم إنشاء الحلقة وإرسالها للاعتماد';

        if (
            $halaqaStatusData['sponsorship_type_id'] !== null ||
            $halaqaStatusData['sponsor_entity'] !== null
        ) {
            $halaqaStatus = HalaqaStatus::create(['halaqa_id' => $halaqa->id] + $halaqaStatusData);
            if ($halaqaStatus) {
                $message .= ' وتم إضافة حالة الحلقة';
            }
        }

        $halaqa->load(['type', 'reference', 'approvalRequest']);

        return $this->success(new HalaqaResource($halaqa), $message, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param  Halaqa  $halaqa
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Halaqa $halaqa)
    {
        $this->authorize('view', $halaqa);
        $halaqa->load(['type', 'reference', 'supervisors.user', 'approvalRequest']);

        if ($request->boolean(key: 'with_students')) {
            $halaqa->load('students');
        }

        return $this->success(
            new HalaqaResource($halaqa),
            'بيانات الحلقة'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param HalaqaRequest $request
     * @param Halaqa $halaqa
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(HalaqaRequest $request, Halaqa $halaqa)
    {
        $halaqaData = $request->validated();
        $halaqaStatusData = [
            'sponsorship_type_id' => $request->input('sponsorship_type_id'), // أو أي نوع كفالة افتراضي إذا كان موجودًا
            'sponsor_entity' => $request->input('sponsor_entity'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'notes' => $request->input('notes'),
        ];

        // unset from_date and to_date from $halaqaData since they are not part of Halaqa model
        unset(
            // $halaqaData['from_date'],
            // $halaqaData['to_date'],
            $halaqaData['sponsorship_type_id'],
            $halaqaData['sponsor_entity'],
            $halaqaData['notes'],
            $halaqaData['teacher_id'],
        );

        $halaqa->update($halaqaData);

        // تعديل المعلم المنسّب للحلقة
        // ملاحظة: نستخدم filled() وليس has() عمداً — إرسال teacher_id فارغاً/null
        // (كما يحدث مثلاً عند تعطيل الحلقة وإخفاء حقل المعلم في الواجهة) يجب ألا يُفسَّر
        // كطلب لإلغاء تنسيب المعلم الحالي، بل يُتجاهل ويبقى المعلم كما هو.
        if ($request->filled('teacher_id')) {
            $newTeacherId = $request->input('teacher_id');

            UserScope::where('scope_type', 'halaqa')
                ->where('scope_id', $halaqa->id)
                ->whereNull('to_date')
                ->update(['to_date' => now()]);

            $this->assignTeacherToHalaqa(User::findOrFail($newTeacherId), $halaqa);
        }

        $message = 'تم تحديث بيانات الحلقة بنجاح';
        if (
            $halaqaStatusData['sponsorship_type_id'] !== null ||
            $halaqaStatusData['sponsor_entity'] !== null
        ) {
            $halaqaStatus = HalaqaStatus::updateOrCreate(['halaqa_id' => $halaqa->id], $halaqaStatusData);
            if ($halaqaStatus) {
                $message .= ' وتم تحديث حالة الحلقة بنجاح';
            }
        }

        $halaqa->load(['type', 'reference', 'supervisors.user']);

        return $this->success(
            new HalaqaResource($halaqa),
            $message
        );
    }

    /**
     * تنسيب معلم لحلقة، مع ضمان أن المعلم لا يبقى منسّباً لأي حلقة أخرى
     * (معلم واحد = حلقة واحدة).
     */
    private function assignTeacherToHalaqa(User $teacher, Halaqa $halaqa): void
    {
        UserScope::where('scope_type', 'halaqa')
            ->where('user_id', $teacher->id)
            ->where('scope_id', '!=', $halaqa->id)
            ->whereNull('to_date')
            ->update(['to_date' => now()]);

        $teacher->assignScope('halaqa', $halaqa->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Halaqa  $halaqa
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Halaqa $halaqa)
    {
        $this->authorize('delete', $halaqa);
        if ($halaqa->students()->exists()) {
            return $this->error(
                'لا يمكن حذف الحلقة لأنها تحتوي على طلاب',
                400
            );
        }

        $halaqa->delete();

        return $this->success(
            null,
            'تم حذف الحلقة بنجاح'
        );
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  Halaqa  $halaqa
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Halaqa $halaqa)
    {
        $this->authorize('restore', $halaqa);
        $halaqa->restore();

        return $this->success(
            new HalaqaResource($halaqa),
            'تم استعادة الحلقة بنجاح'
        );
    }

    public function export()
    {
        return Excel::download(new HalaqaExport, 'halaqas.xlsx');
    }
}
