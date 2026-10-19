<?php

namespace App\Services;

use App\Models\Quran\Surah;
use App\Models\Quran\Ayah;

class QuranCalculatorService
{
    /**
     * حساب عدد الآيات بين نقطتين (من سورة/آية إلى سورة/آية)
     */
    public function calculateAyahsCount(int $fromSurah, int $fromAyah, int $toSurah, int $toAyah): int
    {
        // نطاق تنازلي (من سورة متأخرة إلى سورة سابقة): مجموع مقاطعه
        if ($fromSurah > $toSurah) {
            return array_sum(array_map(
                fn($segment) => $this->calculateAyahsCount(...$segment),
                $this->segments($fromSurah, $fromAyah, $toSurah, $toAyah)
            ));
        }

        if ($fromSurah === $toSurah) {
            return ($toAyah - $fromAyah) + 1;
        }

        $versesCount = Surah::pluck('verses_count', 'id'); // [1 => 7, 2 => 286, ...]

        // باقي السورة الأولى
        $total = ($versesCount[$fromSurah] - $fromAyah) + 1;

        // السور الكاملة بينهما
        for ($s = $fromSurah + 1; $s < $toSurah; $s++) {
            $total += $versesCount[$s];
        }

        // من بداية السورة الأخيرة حتى to_ayah
        $total += $toAyah;

        return $total;
    }

    /**
     * حساب عدد الصفحات (بدقة، مع كسور الصفحة) بالاستفادة من جدول quran_verses
     */
    public function calculatePagesCount(int $fromSurah, int $fromAyah, int $toSurah, int $toAyah): float
    {
        // نطاق تنازلي (من سورة متأخرة إلى سورة سابقة): مجموع مقاطعه
        if ($fromSurah > $toSurah) {
            return round(array_sum(array_map(
                fn($segment) => $this->calculatePagesCount(...$segment),
                $this->segments($fromSurah, $fromAyah, $toSurah, $toAyah)
            )), 2);
        }

        $firstVerse = Ayah::where('surah_id', $fromSurah)
                                ->where('number', $fromAyah)
                                ->first();

        $lastVerse = Ayah::where('surah_id', $toSurah)
                               ->where('number', $toAyah)
                               ->first();

        if (!$firstVerse || !$lastVerse) {
            return 0;
        }

        // نفس الصفحة - نحسب النسبة الجزئية فقط
        if ($firstVerse->page_id === $lastVerse->page_id) {
            $percentage = $lastVerse->end_percentage_in_page - $firstVerse->start_percentage_in_page;
            return round(max($percentage, 0) / 100, 2);
        }

        // صفحات مختلفة
        $fullPagesBetween = $lastVerse->page_id - $firstVerse->page_id - 1;

        $firstPagePortion = round((100 - $firstVerse->start_percentage_in_page) / 100, 2);
        $lastPagePortion  = round($lastVerse->end_percentage_in_page / 100, 2);

        return $firstPagePortion + $fullPagesBetween + $lastPagePortion;
    }

    /**
     * التحقق من صحة نطاق الآيات المُدخل
     *
     * في التنازلي تسير السور من الأخيرة للأولى والآيات داخل السورة من أولها،
     * فالنهاية تكون في نفس السورة أو في سورة سابقة.
     *
     * @return array<string, string> أخطاء بصيغة [field => message]، فاضية لو كل شيء سليم
     */
    public function validateRange(int $fromSurah, int $fromAyah, int $toSurah, int $toAyah, bool $descending = false): array
    {
        $errors = [];

        $fromSurahModel = Surah::find($fromSurah);
        $toSurahModel   = Surah::find($toSurah);

        if (!$fromSurahModel) {
            $errors['from_surah'] = 'رقم السورة غير صحيح';
        } elseif ($fromAyah > $fromSurahModel->verses_count) {
            $errors['from_ayah'] = 'رقم الآية يتجاوز عدد آيات السورة المحددة';
        }

        if (!$toSurahModel) {
            $errors['to_surah'] = 'رقم السورة غير صحيح';
        } elseif ($toAyah > $toSurahModel->verses_count) {
            $errors['to_ayah'] = 'رقم الآية يتجاوز عدد آيات السورة المحددة';
        }

        $wrongSurahOrder = $descending ? $fromSurah < $toSurah : $fromSurah > $toSurah;

        if ($wrongSurahOrder || ($fromSurah === $toSurah && $fromAyah > $toAyah)) {
            $errors['to_surah'] = $descending
                ? 'نقطة النهاية يجب أن تكون بعد نقطة البداية حسب الترتيب التنازلي (نفس السورة أو سورة سابقة)'
                : 'نقطة النهاية يجب أن تكون بعد نقطة البداية';
        }

        return $errors;
    }

    /**
     * تقسيم النطاق إلى مقاطع متصلة بترتيب المصحف، كل مقطع [من سورة، من آية، إلى سورة، إلى آية].
     *
     * النطاق التصاعدي مقطع واحد. النطاق التنازلي (مثل: الفلق 3 ← الإخلاص 2) يعني:
     * بقية سورة البداية، ثم السور التي بينهما كاملة، ثم سورة النهاية من أولها حتى آية النهاية.
     *
     * @return array<int, array{0:int,1:int,2:int,3:int}>
     */
    public function segments(int $fromSurah, int $fromAyah, int $toSurah, int $toAyah): array
    {
        if ($fromSurah <= $toSurah) {
            return [[$fromSurah, $fromAyah, $toSurah, $toAyah]];
        }

        $versesCount = Surah::pluck('verses_count', 'id');

        $segments = [[$toSurah, 1, $toSurah, $toAyah]];

        if ($fromSurah - $toSurah > 1) {
            $segments[] = [$toSurah + 1, 1, $fromSurah - 1, (int) $versesCount[$fromSurah - 1]];
        }

        $segments[] = [$fromSurah, $fromAyah, $fromSurah, (int) $versesCount[$fromSurah]];

        return $segments;
    }
}