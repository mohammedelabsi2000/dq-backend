<?php

namespace App\Services;

use App\Enums\MemorizationDirection;
use App\Models\DailyAchievement;
use App\Models\LevelTrackSubject;
use App\Models\Quran\Ayah;
use App\Models\Quran\CustomJuz;
use App\Models\Quran\Juz;
use App\Models\Quran\Surah;
use App\Models\StudentPlan;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Support\Collection;

/**
 * ربط الإنجاز اليومي بمواد الطالب الحالية.
 *
 * كل النطاقات هنا عبارة عن فترات [من، إلى] على معرّف الآية في جدول quran_verses
 * (المعرّف متسلسل بترتيب المصحف من 1 إلى 6236).
 *
 * الإنجاز يُحسب لكل سجل طالب في مادة (StudentSubject) فكل خطة مستقلة عن الأخرى.
 * مسار الحفظ يتكوّن من أجزاء: اتجاه المادة يحدد ترتيب الأجزاء، واتجاه كل جزء يحدد
 * ترتيب السور داخله (الجزء غير المخصَّص يرث اتجاه المادة).
 */
class SubjectProgressService
{
    public function __construct(
        protected QuranCalculatorService $calculator
    ) {}

    /**
     * خطط الطالب النشطة (الرئيسية أولاً) - قد يكون للطالب أكثر من خطة في نفس الوقت
     *
     * @return Collection<int, StudentPlan>
     */
    public function activePlans(int $studentId): Collection
    {
        return StudentPlan::byStudent($studentId)
            ->active()
            ->with(['plan', 'currentLevel'])
            ->orderByDesc('is_main')
            ->orderBy('from_date')
            ->get();
    }

    /**
     * المواد التي يدرسها الطالب حالياً (قيد الدراسة) في المستوى الحالي لكل خطة نشطة.
     * كل سجل يحمل خطته في العلاقة studentPlan.
     *
     * @return Collection<int, StudentSubject>
     */
    public function currentStudentSubjects(int $studentId): Collection
    {
        $plans = $this->activePlans($studentId)->keyBy('current_level_id');

        if ($plans->isEmpty()) {
            return collect();
        }

        return StudentSubject::where('student_id', $studentId)
            ->whereIn('level_id', $plans->keys())
            ->whereNull('to_date')
            ->where(function ($q) {
                $q->whereNull('result_status_id')
                    ->orWhereHas('resultStatus', fn($q) => $q->where('const_key', 'in_progress'));
            })
            ->with('subject.subjectType')
            ->get()
            ->filter(fn($studentSubject) => $studentSubject->subject)
            ->each(fn($studentSubject) => $studentSubject->setRelation('studentPlan', $plans[$studentSubject->level_id]))
            ->sortBy(fn($studentSubject) => $plans->keys()->search($studentSubject->level_id))
            ->values();
    }

    /**
     * تحديد سجل الطالب الحالي في المادة. لو كان الطالب يدرس نفس المادة في أكثر من خطة
     * يجب تحديد السجل صراحةً.
     *
     * @return array{student_subject?: StudentSubject, field?: string, error?: string}
     */
    public function resolveStudentSubject(int $studentId, int $subjectId, ?int $studentSubjectId = null): array
    {
        $candidates = $this->currentStudentSubjects($studentId)->where('subject_id', $subjectId)->values();

        if ($studentSubjectId) {
            $match = $candidates->firstWhere('id', $studentSubjectId);

            return $match
                ? ['student_subject' => $match]
                : ['field' => 'student_subject_id', 'error' => 'سجل المادة المحدد ليس من المواد الحالية للطالب'];
        }

        if ($candidates->isEmpty()) {
            return ['field' => 'subject_id', 'error' => 'هذه المادة ليست من المواد الحالية للطالب'];
        }

        if ($candidates->count() > 1) {
            return ['field' => 'student_subject_id', 'error' => 'الطالب يدرس هذه المادة في أكثر من خطة؛ يجب تحديد سجل المادة (student_subject_id)'];
        }

        return ['student_subject' => $candidates->first()];
    }

    /**
     * إعدادات اتجاه الحفظ للمادة في خطة الطالب: تؤخذ من ربط المادة بمسار المستوى،
     * وإن لم تُحدَّد هناك فمن المادة نفسها.
     *
     * @return array{direction: string, juz_directions: array<int|string, string>}
     */
    public function settings(StudentSubject $studentSubject): array
    {
        $link = $studentSubject->level_id
            ? LevelTrackSubject::where('subject_id', $studentSubject->subject_id)
                ->whereHas('levelTrack', fn($q) => $q->where('level_id', $studentSubject->level_id))
                ->first()
            : null;

        return [
            'direction' => $link?->memorization_direction
                ?: ($studentSubject->subject->memorization_direction ?: MemorizationDirection::Ascending->value),
            'juz_directions' => $link?->juz_directions ?: [],
        ];
    }

    /**
     * مسار حفظ المادة: الأجزاء بترتيب حفظها، ولكل جزء نطاقه واتجاهه.
     *
     * @return array{direction: string, juz_directions: array, segments: array<int, array{juz: ?array, interval: array{0:int,1:int}, direction: string}>}
     */
    public function path(StudentSubject $studentSubject): array
    {
        $subject = $studentSubject->subject;
        $settings = $this->settings($studentSubject);
        $segments = [];

        if ($customJuzIds = $this->decodeIds($subject->custom_juz_id)) {
            foreach (CustomJuz::whereIn('id', $customJuzIds)->get() as $juz) {
                $interval = $this->interval($juz->start_surah_id, $juz->start_aya, $juz->end_surah_id, $juz->end_aya);

                if ($interval) {
                    $segments[] = [
                        'juz' => ['id' => $juz->id, 'name' => $juz->name],
                        'interval' => $interval,
                        'direction' => $settings['juz_directions'][$juz->id] ?? $settings['direction'],
                    ];
                }
            }
        }

        // مادة بلا أجزاء مخصصة: مقطع واحد لكل نطاق متصل باتجاه المادة
        if (!$segments) {
            foreach ($this->subjectIntervals($subject) ?? [[1, (int) Ayah::max('id')]] as $interval) {
                $segments[] = ['juz' => null, 'interval' => $interval, 'direction' => $settings['direction']];
            }
        }

        usort($segments, fn($a, $b) => $a['interval'][0] <=> $b['interval'][0]);

        if ($this->isDescending($settings['direction'])) {
            $segments = array_reverse($segments);
        }

        return $settings + ['segments' => $segments];
    }

    /**
     * حالة تقدّم الطالب في المسار: المتبقي من كل جزء، الجزء الحالي، نقطة البداية،
     * وأقصى نطاق يمكن أن يغطيه الإنجاز القادم (run).
     *
     * الإنجاز الواحد لا يعبر إلى جزء مختلف الاتجاه، فالـ run هو المتبقي من الجزء الحالي
     * مع الأجزاء التالية ما دامت كلها باتجاه المادة.
     */
    public function state(StudentSubject $studentSubject, string $achievementType, ?int $exceptId = null): array
    {
        $path = $this->path($studentSubject);
        $recorded = $this->recordedIntervals($studentSubject->id, $achievementType, $exceptId);
        $segments = $path['segments'];
        $current = null;

        foreach ($segments as $index => $segment) {
            $segments[$index]['remaining'] = $this->subtract([$segment['interval']], $recorded);

            if ($current === null && $segments[$index]['remaining']) {
                $current = $index;
            }
        }

        $run = [];

        if ($current !== null) {
            $run = $segments[$current]['remaining'];

            if ($segments[$current]['direction'] === $path['direction']) {
                for ($index = $current + 1; $index < count($segments); $index++) {
                    if ($segments[$index]['direction'] !== $path['direction']) {
                        break;
                    }

                    array_push($run, ...$segments[$index]['remaining']);
                }
            }
        }

        return [
            'direction' => $path['direction'],
            'segments' => $segments,
            'current' => $current,
            'next' => $current !== null
                ? $this->nextStart($segments[$current]['remaining'], $segments[$current]['direction'])
                : null,
            'run' => $this->merge($run),
            'recorded' => $recorded,
        ];
    }

    /**
     * اتجاه الحفظ عند آية معيّنة (اتجاه الجزء الذي تقع فيه)
     */
    public function directionAt(StudentSubject $studentSubject, int $surah, int $ayah): string
    {
        $path = $this->path($studentSubject);
        $id = (int) Ayah::where('surah_id', $surah)->where('number', $ayah)->value('id');

        foreach ($path['segments'] as $segment) {
            if ($id >= $segment['interval'][0] && $id <= $segment['interval'][1]) {
                return $segment['direction'];
            }
        }

        return $path['direction'];
    }

    /**
     * نطاق المادة من القرآن. null لو المادة غير مقيّدة بنطاق.
     *
     * @return array<int, array{0:int,1:int}>|null
     */
    public function subjectIntervals(Subject $subject): ?array
    {
        $intervals = [];

        // 1. الأجزاء المخصصة
        $customJuzIds = $this->decodeIds($subject->custom_juz_id);
        if ($customJuzIds) {
            foreach (CustomJuz::whereIn('id', $customJuzIds)->get() as $juz) {
                $intervals[] = $this->interval($juz->start_surah_id, $juz->start_aya, $juz->end_surah_id, $juz->end_aya);
            }
        }

        // 2. السور
        if (!$intervals && ($surahIds = $this->decodeIds($subject->surahs))) {
            foreach (Surah::whereIn('id', $surahIds)->get() as $surah) {
                $intervals[] = $this->interval($surah->id, 1, $surah->id, $surah->verses_count);
            }
        }

        // 3. نطاق الأجزاء من عنوان المادة: "الأجزاء من (28 - 30)"
        if (!$intervals && preg_match('/الأجزاء\s+من\s*\((\d+)\s*-\s*(\d+)\)/u', (string) $subject->title, $m)) {
            foreach (Juz::whereBetween('id', [min($m[1], $m[2]), max($m[1], $m[2])])->get() as $juz) {
                $intervals[] = $this->interval($juz->start_surah_id, $juz->start_aya, $juz->end_surah_id, $juz->end_aya);
            }
        }

        $intervals = $this->merge(array_filter($intervals));

        return $intervals ?: null;
    }

    /**
     * ما سُجّل للطالب في هذا السجل (المادة في خطة معيّنة) من نفس نوع الإنجاز، ما عدا ما حالته "إعادة"
     *
     * @return array<int, array{0:int,1:int}>
     */
    public function recordedIntervals(int $studentSubjectId, string $achievementType, ?int $exceptId = null): array
    {
        $achievements = DailyAchievement::where('student_subject_id', $studentSubjectId)
            ->where('achievement_type', $achievementType)
            ->where('achievement_status', '!=', 'retry')
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->get(['from_surah', 'from_ayah', 'to_surah', 'to_ayah']);

        $intervals = [];
        foreach ($achievements as $a) {
            array_push($intervals, ...$this->achievementIntervals($a->from_surah, $a->from_ayah, $a->to_surah, $a->to_ayah));
        }

        return $this->merge($intervals);
    }

    /**
     * فترات نطاق إنجاز. النطاق التنازلي (من سورة متأخرة إلى سورة سابقة) يتكوّن من أكثر من فترة.
     *
     * @return array<int, array{0:int,1:int}>
     */
    public function achievementIntervals(int $fromSurah, int $fromAyah, int $toSurah, int $toAyah): array
    {
        $intervals = array_map(
            fn($segment) => $this->interval(...$segment),
            $this->calculator->segments($fromSurah, $fromAyah, $toSurah, $toAyah)
        );

        return array_values(array_filter($intervals));
    }

    /**
     * التحقق من أن النطاق المُدخل داخل المادة وغير مسجّل مسبقاً ويبدأ من نقطة البداية المطلوبة
     * ولا يعبر إلى جزء مختلف الاتجاه
     *
     * @return array<string, string> أخطاء بصيغة [field => message]
     */
    public function validateAchievementRange(
        StudentSubject $studentSubject,
        string $achievementType,
        int $fromSurah,
        int $fromAyah,
        int $toSurah,
        int $toAyah,
        ?int $exceptId = null
    ): array {
        $ranges = $this->achievementIntervals($fromSurah, $fromAyah, $toSurah, $toAyah);

        if (!$ranges) {
            return [];
        }

        $state = $this->state($studentSubject, $achievementType, $exceptId);
        $whole = $this->merge(array_column($state['segments'], 'interval'));

        if ($this->subtract($ranges, $whole)) {
            return ['to_surah' => 'النطاق المحدد خارج نطاق المادة (' . $studentSubject->subject->title . ')'];
        }

        foreach ($ranges as $range) {
            foreach ($state['recorded'] as [$start, $end]) {
                if ($range[0] <= $end && $range[1] >= $start) {
                    return ['to_surah' => 'النطاق المحدد يتضمن آيات تم تسجيل إنجازها مسبقاً لهذا الطالب'];
                }
            }
        }

        if (!$state['next']) {
            return ['to_surah' => 'تم تسجيل إنجاز كامل هذه المادة لهذا الطالب'];
        }

        // التحقق من الترتيب حسب اتجاه الجزء الحالي
        $next = $state['next'];
        $segment = $state['segments'][$state['current']];
        $startId = (int) Ayah::where('surah_id', $fromSurah)->where('number', $fromAyah)->value('id');

        if ($startId !== $next['id']) {
            return ['from_ayah' => "يجب أن يبدأ الإنجاز من سورة {$next['surah_name']} آية {$next['ayah']} حسب ترتيب المادة ("
                . ($this->isDescending($segment['direction']) ? 'تنازلي' : 'تصاعدي') . ')'];
        }

        // الإنجاز الواحد لا يعبر إلى جزء مختلف الاتجاه
        if ($this->subtract($ranges, $state['run'])) {
            return ['to_surah' => 'لا يمكن أن يعبر الإنجاز الواحد إلى جزء باتجاه حفظ مختلف؛ يجب إنهاء '
                . ($segment['juz'] ? 'جزء ' . $segment['juz']['name'] : 'الجزء الحالي') . ' أولاً'];
        }

        return [];
    }

    /**
     * نقطة البداية المطلوبة للإنجاز القادم حسب اتجاه المادة:
     * - تصاعدي: أول آية متبقية بترتيب المصحف.
     * - تنازلي: السور من الأخيرة للأولى، والآيات داخل السورة من أولها؛
     *   أي أول آية متبقية من آخر سورة متبقية.
     *
     * @param  array<int, array{0:int,1:int}>  $remaining
     * @return array{id:int,surah:int,surah_name:?string,ayah:int,page:int}|null
     */
    public function nextStart(array $remaining, ?string $direction): ?array
    {
        if (!$remaining) {
            return null;
        }

        $id = $remaining[0][0];

        if ($this->isDescending($direction)) {
            [$start, $end] = end($remaining);
            $surahId = Ayah::whereKey($end)->value('surah_id');
            $surahFirstId = (int) Ayah::where('surah_id', $surahId)->where('number', 1)->value('id');
            $id = max($start, $surahFirstId);
        }

        $verse = Ayah::find($id, ['id', 'surah_id', 'number', 'page_id']);

        return [
            'id' => (int) $verse->id,
            'surah' => (int) $verse->surah_id,
            'surah_name' => Surah::whereKey($verse->surah_id)->value('name_ar'),
            'ayah' => (int) $verse->number,
            'page' => (int) $verse->page_id,
        ];
    }

    /**
     * تحديد نطاق الإنجاز من نقطة النهاية فقط: البداية تُحسب تلقائياً من آخر ما سُجّل للطالب.
     *
     * النطاق يُخزَّن باتجاه الحفظ: في التنازلي تكون البداية في سورة متأخرة والنهاية في سورة سابقة
     * (مثلاً من الناس 1 إلى النبأ 40).
     *
     * @return array{range?: array{from_surah:int,from_ayah:int,to_surah:int,to_ayah:int}, error?: string}
     */
    public function resolveRange(StudentSubject $studentSubject, string $achievementType, int $toSurah, int $toAyah): array
    {
        $next = $this->state($studentSubject, $achievementType)['next'];

        if (!$next) {
            return ['error' => 'تم تسجيل إنجاز كامل هذه المادة لهذا الطالب'];
        }

        return ['range' => [
            'from_surah' => $next['surah'],
            'from_ayah' => $next['ayah'],
            'to_surah' => $toSurah,
            'to_ayah' => $toAyah,
        ]];
    }

    /**
     * ملخص المتبقي من المادة للطالب بالشكل المعروض في AvailableSubjectResource
     */
    public function progress(StudentSubject $studentSubject, string $achievementType, ?int $exceptId = null): array
    {
        $state = $this->state($studentSubject, $achievementType, $exceptId);
        $path = $this->path($studentSubject);
        $current = $state['current'] !== null ? $state['segments'][$state['current']] : null;

        // السور المتاحة بترتيب الحفظ: الأجزاء بترتيب المسار، وسور كل جزء حسب اتجاهه
        $surahs = [];
        $availableCount = 0;

        foreach ($state['segments'] as $segment) {
            $described = $this->describe($segment['remaining']);
            $availableCount += $described['ayahs_count'];

            array_push($surahs, ...($this->isDescending($segment['direction'])
                ? array_reverse($described['surahs'])
                : $described['surahs']));
        }

        // نقطة البداية المطلوبة للإنجاز القادم
        $nextStart = $state['next'];
        unset($nextStart['id']);

        return [
            'has_range' => $this->subjectIntervals($studentSubject->subject) !== null,
            'memorization_direction' => $path['direction'],
            'juz_directions' => (object) $path['juz_directions'],
            'total_ayahs_count' => $this->countAyahs($this->merge(array_column($state['segments'], 'interval'))),
            'available_ayahs_count' => $availableCount,
            'is_completed' => $current === null,
            'current_juz' => $current && $current['juz']
                ? $current['juz'] + ['direction' => $current['direction']]
                : null,
            'next_start' => $nextStart,
            'available_range' => $current ? $this->describeRun($state['run'], $nextStart, $current['direction']) : null,
            'available_surahs' => $surahs,
        ];
    }

    /**
     * أقصى نطاق للإنجاز القادم، معروضاً باتجاه الحفظ: من نقطة البداية إلى آخر ما يمكن الوصول إليه
     */
    private function describeRun(array $run, array $nextStart, string $direction): ?array
    {
        $ranges = $this->describe($run)['ranges'];

        if (!$ranges) {
            return null;
        }

        if (!$this->isDescending($direction)) {
            return ['ayahs_count' => $this->countAyahs($run)] + $ranges[0];
        }

        // في التنازلي ينتهي المسار عند آخر آية من أول سورة (بترتيب المصحف) في النطاق
        $lowest = $ranges[0];
        $end = Ayah::where('surah_id', $lowest['from_surah'])
            ->whereBetween('id', $run[0])
            ->orderByDesc('number')
            ->first(['number', 'page_id']);

        return [
            'from_surah' => $nextStart['surah'],
            'from_surah_name' => $nextStart['surah_name'],
            'from_ayah' => $nextStart['ayah'],
            'to_surah' => $lowest['from_surah'],
            'to_surah_name' => $lowest['from_surah_name'],
            'to_ayah' => (int) $end->number,
            'from_page' => $nextStart['page'],
            'to_page' => (int) $end->page_id,
            'ayahs_count' => $this->countAyahs($run),
        ];
    }

    public function isDescending(?string $direction): bool
    {
        return $direction === MemorizationDirection::Descending->value;
    }

    /**
     * تحويل الفترات إلى شكل قابل للعرض: نطاقات وسور
     *
     * @param  array<int, array{0:int,1:int}>  $intervals
     */
    public function describe(array $intervals): array
    {
        $ranges = [];
        $surahs = [];
        $ayahsCount = 0;

        $surahNames = $intervals ? Surah::pluck('name_ar', 'id') : collect();

        foreach ($intervals as [$start, $end]) {
            $verses = Ayah::whereBetween('id', [$start, $end])
                ->orderBy('id')
                ->get(['id', 'surah_id', 'number', 'page_id']);

            if ($verses->isEmpty()) {
                continue;
            }

            $first = $verses->first();
            $last = $verses->last();
            $ayahsCount += $verses->count();

            $ranges[] = [
                'from_surah' => $first->surah_id,
                'from_surah_name' => $surahNames[$first->surah_id] ?? null,
                'from_ayah' => $first->number,
                'to_surah' => $last->surah_id,
                'to_surah_name' => $surahNames[$last->surah_id] ?? null,
                'to_ayah' => $last->number,
                'from_page' => $first->page_id,
                'to_page' => $last->page_id,
                'ayahs_count' => $verses->count(),
            ];

            foreach ($verses->groupBy('surah_id') as $surahId => $surahVerses) {
                $surahs[$surahId] ??= [
                    'id' => (int) $surahId,
                    'name_ar' => $surahNames[$surahId] ?? null,
                    'ranges' => [],
                ];

                $surahs[$surahId]['ranges'][] = [
                    'from_ayah' => $surahVerses->first()->number,
                    'to_ayah' => $surahVerses->last()->number,
                ];
            }
        }

        ksort($surahs);

        return [
            'ayahs_count' => $ayahsCount,
            'ranges' => $ranges,
            'surahs' => array_values($surahs),
        ];
    }

    public function countAyahs(array $intervals): int
    {
        return array_sum(array_map(fn($i) => $i[1] - $i[0] + 1, $intervals));
    }

    // ========================
    // Helpers
    // ========================

    private function interval(int $fromSurah, int $fromAyah, int $toSurah, int $toAyah): ?array
    {
        $start = Ayah::where('surah_id', $fromSurah)->where('number', $fromAyah)->value('id');
        $end = Ayah::where('surah_id', $toSurah)->where('number', $toAyah)->value('id');

        if (!$start || !$end || $start > $end) {
            return null;
        }

        return [(int) $start, (int) $end];
    }

    private function decodeIds($value): array
    {
        $ids = is_array($value) ? $value : json_decode($value ?? '[]', true);

        return is_array($ids) ? array_values(array_filter($ids, 'is_numeric')) : [];
    }

    private function merge(array $intervals): array
    {
        usort($intervals, fn($a, $b) => $a[0] <=> $b[0]);

        $merged = [];
        foreach ($intervals as $interval) {
            $last = count($merged) - 1;

            if ($last >= 0 && $interval[0] <= $merged[$last][1] + 1) {
                $merged[$last][1] = max($merged[$last][1], $interval[1]);
            } else {
                $merged[] = $interval;
            }
        }

        return $merged;
    }

    private function subtract(array $intervals, array $removed): array
    {
        $result = [];

        foreach ($intervals as [$start, $end]) {
            foreach ($removed as [$removedStart, $removedEnd]) {
                if ($removedEnd < $start || $removedStart > $end) {
                    continue;
                }

                if ($removedStart > $start) {
                    $result[] = [$start, $removedStart - 1];
                }

                $start = $removedEnd + 1;

                if ($start > $end) {
                    break;
                }
            }

            if ($start <= $end) {
                $result[] = [$start, $end];
            }
        }

        return $result;
    }
}
