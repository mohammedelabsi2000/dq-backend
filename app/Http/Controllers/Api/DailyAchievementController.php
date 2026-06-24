<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyAchievement\StoreDailyAchievementRequest;
use App\Http\Requests\DailyAchievement\UpdateDailyAchievementRequest;
use App\Http\Resources\DailyAchievementResource;
use App\Models\DailyAchievement;
use Illuminate\Http\Request;

class DailyAchievementController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorize('viewAny', DailyAchievement::class);
        
        $query = DailyAchievement::query()
            ->with(['student', 'teacher']);

        // Filter by student
        if ($request->has('student_id')) {
            $query->byStudent($request->student_id);
        }

        // Filter by teacher
        if ($request->has('teacher_id')) {
            $query->byTeacher($request->teacher_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->byDate($request->date);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->byDateRange($request->from_date, $request->to_date);
        }

        // Filter by achievement type
        if ($request->has('achievement_type')) {
            $query->byType(\App\Enums\AchievementType::from($request->achievement_type));
        }

        // Filter by status
        if ($request->has('achievement_status')) {
            $query->byStatus(\App\Enums\AchievementStatus::from($request->achievement_status));
        }

        // Apply ordering and pagination
        $q = $this->applyFilters($query, [
            'searchColumns' => ['date'],
            'orderColumn' => 'date',
            'orderDirection' => 'desc'
        ]);

        $achievements = $q['query']->get();
        $total = $q['count'];

        return $this->successWithPagination(
            DailyAchievementResource::collection($achievements),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    public function store(StoreDailyAchievementRequest $request)
    {
        // $this->authorize('create', DailyAchievement::class);

        $data = $request->validated();
        // $data['created_by'] = auth()->id();
        // $data['recorded_at'] = now();

        $achievement = DailyAchievement::create($data);
        $achievement->load(['student', 'teacher']);

        return $this->success(
            new DailyAchievementResource($achievement),
            'تم إنشاء إنجاز الحفظ بنجاح',
            201
        );
    }

    public function show(DailyAchievement $dailyAchievement)
    {
        // $this->authorize('view', $dailyAchievement);
        
        $dailyAchievement->load(['student', 'teacher']);

        return $this->success(
            new DailyAchievementResource($dailyAchievement),
            'success',
            200
        );
    }

    public function update(UpdateDailyAchievementRequest $request, DailyAchievement $dailyAchievement)
    {
        // $this->authorize('update', $dailyAchievement);

        $data = $request->validated();

        $dailyAchievement->update($data);
        $dailyAchievement->load(['student', 'teacher']);

        return $this->success(
            new DailyAchievementResource($dailyAchievement),
            'تم تحديث إنجاز الحفظ بنجاح'
        );
    }

    public function destroy(DailyAchievement $dailyAchievement)
    {
        // $this->authorize('delete', $dailyAchievement);
        
        $dailyAchievement->delete();

        return $this->success(
            null,
            'تم حذف إنجاز الحفظ بنجاح'
        );
    }

    public function studentAchievements(Request $request, $studentId)
    {
        // $this->authorize('viewAny', DailyAchievement::class);

        $query = DailyAchievement::query()
            ->byStudent($studentId)
            ->with(['teacher']);

        // Filter by date range
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->byDateRange($request->from_date, $request->to_date);
        }

        $achievements = $query->orderBy('date', 'desc')->get();

        return $this->success(
            DailyAchievementResource::collection($achievements),
            'success',
            200
        );
    }

    // public function statistics(Request $request)
    // {
    //     // $this->authorize('viewAny', DailyAchievement::class);

    //     $query = DailyAchievement::query();

    //     if ($request->has('student_id')) {
    //         $query->byStudent($request->student_id);
    //     }

    //     if ($request->has('from_date') && $request->has('to_date')) {
    //         $query->byDateRange($request->from_date, $request->to_date);
    //     }

    //     $stats = [
    //         'total_achievements' => $query->count(),
    //         // 'total_ayahs' => $query->sum('ayah_count'),
    //         'total_mistakes' => $query->sum('mistakes_count'),
    //         'by_type' => $query->selectRaw('achievement_type, COUNT(*) as count')
    //             ->groupBy('achievement_type')
    //             ->get()
    //             ->mapWithKeys(function ($item) {
    //                 return [\App\Enums\AchievementType::from($item->achievement_type)->getLabel() => $item->count];
    //             }),
    //         'by_grade' => $query->selectRaw('evaluation_grade, COUNT(*) as count')
    //             ->groupBy('evaluation_grade')
    //             ->get()
    //             ->mapWithKeys(function ($item) {
    //                 return [\App\Enums\EvaluationGrade::from($item->evaluation_grade)->getLabel() => $item->count];
    //             }),
    //         'by_status' => $query->selectRaw('achievement_status, COUNT(*) as count')
    //             ->groupBy('achievement_status')
    //             ->get()
    //             ->mapWithKeys(function ($item) {
    //                 return [\App\Enums\AchievementStatus::from($item->achievement_status)->getLabel() => $item->count];
    //             }),
    //     ];

    //     return $this->success(
    //         $stats,
    //         'success',
    //         200
    //     );
    // }
}
