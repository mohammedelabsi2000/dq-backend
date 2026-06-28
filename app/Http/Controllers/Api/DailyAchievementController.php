<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyAchievement\StoreDailyAchievementRequest;
use App\Http\Requests\DailyAchievement\UpdateDailyAchievementRequest;
use App\Http\Resources\DailyAchievementResource;
use App\Http\Resources\StudentResource;
use App\Models\DailyAchievement;
use App\Models\Student;
use App\Services\QuranCalculatorService;
use Illuminate\Http\Request;

class DailyAchievementController extends Controller
{

    public function __construct(
        protected QuranCalculatorService $calculator
    ) {}
    public function index(Request $request)
    {
        $this->authorize('viewAny', DailyAchievement::class);

        $query = DailyAchievement::query()
            ->visibleTo(auth()->user())
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
        $this->authorize('create', DailyAchievement::class);

        $data = $request->validated();
        $data['teacher_id'] = auth()->id();
        // $data['created_by'] = auth()->id();
        // $data['recorded_at'] = now();

        // حساب عدد الآيات والصفحات تلقائياً
        $data['ayahs_count'] = $this->calculator->calculateAyahsCount(
            $data['from_surah'],
            $data['from_ayah'],
            $data['to_surah'],
            $data['to_ayah']
        );

        $data['pages_count'] = $this->calculator->calculatePagesCount(
            $data['from_surah'],
            $data['from_ayah'],
            $data['to_surah'],
            $data['to_ayah']
        );

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
        $this->authorize('view', $dailyAchievement);

        $dailyAchievement->load(['student', 'teacher']);

        return $this->success(
            new DailyAchievementResource($dailyAchievement),
            'success',
            200
        );
    }

    public function update(UpdateDailyAchievementRequest $request, DailyAchievement $daily_memorization)
    {
        $this->authorize('update', $daily_memorization);

        $data = $request->validated();

          // إعادة حساب عدد الآيات والصفحات لو تغيّر نطاق السور/الآيات
        $rangeChanged = $request->filled('from_surah') || $request->filled('from_ayah')
                     || $request->filled('to_surah') || $request->filled('to_ayah');
 
        if ($rangeChanged) {
            $fromSurah = $data['from_surah'] ?? $daily_memorization->from_surah;
            $fromAyah  = $data['from_ayah']  ?? $daily_memorization->from_ayah;
            $toSurah   = $data['to_surah']   ?? $daily_memorization->to_surah;
            $toAyah    = $data['to_ayah']    ?? $daily_memorization->to_ayah;
 
            $data['ayahs_count'] = $this->calculator->calculateAyahsCount(
                $fromSurah, $fromAyah, $toSurah, $toAyah
            );
 
            $data['pages_count'] = $this->calculator->calculatePagesCount(
                $fromSurah, $fromAyah, $toSurah, $toAyah
            );
        }

        
        $daily_memorization->update($data);
        $daily_memorization->load(['student', 'teacher']);

        return $this->success(
            new DailyAchievementResource($daily_memorization),
            'تم تحديث إنجاز الحفظ بنجاح'
        );
    }

    public function destroy(DailyAchievement $daily_memorization)
    {
        $this->authorize('delete', $daily_memorization);

        $daily_memorization->delete();

        return $this->success(
            null,
            'تم حذف إنجاز الحفظ بنجاح'
        );
    }

    public function studentAchievements(Request $request, $studentId)
    {
        $this->authorize('viewAny', DailyAchievement::class);

        $student = Student::findOrFail($studentId);

        $query = DailyAchievement::query()
            ->byStudent($studentId)
            ->visibleTo(auth()->user())
            ->with(['teacher']);

        // Filter by date range
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->byDateRange($request->from_date, $request->to_date);
        }

        // Total achievements
        $totalAchievements = $query->count();

        $achievements = $query->orderBy('date', 'desc')->get();

        $data = [
            'achievements' => DailyAchievementResource::collection($achievements),
            'student' => new StudentResource($student)
        ];
        $total = $query->count();

        return $this->successWithPagination(
            $data,
            ['total' => $total],
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
