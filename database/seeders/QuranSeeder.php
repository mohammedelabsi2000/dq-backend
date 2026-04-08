<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuranSeeder extends Seeder
{
    protected string $baseUrl = 'https://api.quran.com/api/v4';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Disable foreign key constraints to allow seeding because of the relations
            Schema::disableForeignKeyConstraints();

            $this->seedSurahs();
            $this->seedVersesAndRelations();

            $this->calculatePercentages();
            $this->buildJuz();

            Schema::enableForeignKeyConstraints();
        });
    }

    /**
     * Seed surahs from Quran.com API
     * 
     * @return void
     */
    protected function seedSurahs()
    {
        $response = Http::get($this->baseUrl . '/chapters')->json();

        foreach ($response['chapters'] as $surah) {
            DB::table('quran_surahs')->updateOrInsert(
                ['id' => $surah['id']],
                [
                    'name_ar' => $surah['name_arabic'],
                    'name_en' => $surah['name_simple'],
                    'name_transliteration' => $surah['name_complex'],
                    'revelation_place' => $surah['revelation_place'] === 'makkah' ? 1 : 2,
                    'revelation_place_ar' => $surah['revelation_place'] === 'makkah' ? 'مكية' : 'مدنية',
                    'revelation_place_en' => $surah['revelation_place'],
                    'verses_count' => $surah['verses_count'],
                    'words_count' => 0,
                    'letters_count' => 0,
                ]
            );
        }
    }

    /**
     * Seed verses and relations from Quran.com API
     * 
     * @return void
     */
    protected function seedVersesAndRelations()
    {
        $pageStats = [];
        $surahStats = [];

        for ($surah = 1; $surah <= 114; $surah++) {

            $response = Http::get($this->baseUrl . "/verses/by_chapter/$surah", [
                'language' => 'en',
                'words' => false,
                'per_page' => 300,
                'fields' => 'text_uthmani'
            ])->json();

            foreach ($response['verses'] as $verse) {

                $text = $verse['text_uthmani'];
                $letters = mb_strlen(preg_replace('/\s+/', '', $text));
                $words = str_word_count(strip_tags($text));

                $page = $verse['page_number'];

                // تجميع الصفحة
                $pageStats[$page]['letters'] = ($pageStats[$page]['letters'] ?? 0) + $letters;

                // تجميع السورة
                $surahStats[$surah]['letters'] = ($surahStats[$surah]['letters'] ?? 0) + $letters;
                $surahStats[$surah]['words'] = ($surahStats[$surah]['words'] ?? 0) + $words;

                DB::table('quran_verses')->updateOrInsert(
                    [
                        'surah_id' => $surah,
                        'number' => $verse['verse_number'],
                    ],
                    [
                        'text_ar' => $text,
                        'text_en' => $verse['translations'][0]['text'] ?? null,
                        'juz_id' => $verse['juz_number'],
                        'page_id' => $page,
                        'sajda' => $verse['sajdah_number'] ? 1 : 0,
                        'letters_count' => $letters,
                    ]
                );
            }
        }

        $this->updateSurahStats($surahStats);
        $this->buildPages($pageStats);
    }

    /**
     * Update surah stats
     * 
     * @param array $stats
     * @return void
     */
    protected function updateSurahStats($stats)
    {
        foreach ($stats as $surah => $data) {
            DB::table('quran_surahs')
                ->where('id', $surah)
                ->update([
                    'letters_count' => $data['letters'],
                    'words_count' => $data['words'],
                ]);
        }
    }

    /**
     * Build pages from verse data
     * 
     * @param array $pageStats
     * @return void
     */
    protected function buildPages($pageStats)
    {
        foreach ($pageStats as $page => $data) {

            $first = DB::table('quran_verses')->where('page_id', $page)->first();
            $last = DB::table('quran_verses')->where('page_id', $page)->orderByDesc('id')->first();

            DB::table('quran_pages')->updateOrInsert(
                ['id' => $page],
                [
                    'start_surah_id' => $first->surah_id,
                    'start_aya' => $first->number,
                    'end_surah_id' => $last->surah_id,
                    'end_aya' => $last->number,
                    'letters_count' => $data['letters'],
                ]
            );
        }
    }

    /**
     * Calculate percentages for verses
     * 
     * @return void
     */
    protected function calculatePercentages()
    {
        $pages = DB::table('quran_pages')->get();

        foreach ($pages as $page) {

            $verses = DB::table('quran_verses')
                ->where('page_id', $page->id)
                ->orderBy('id')
                ->get();

            $cumulative = 0;

            foreach ($verses as $verse) {

                $start = $cumulative;
                $cumulative += $verse->letters_count;
                $end = $cumulative;

                DB::table('quran_verses')
                    ->where('id', $verse->id)
                    ->update([
                        'cumulative_before' => $start,
                        'cumulative_total' => $end,
                        'start_percentage_in_page' => ($start / $page->letters_count) * 100,
                        'end_percentage_in_page' => ($end / $page->letters_count) * 100,
                        'percentage_in_page' => ($verse->letters_count / $page->letters_count) * 100,
                    ]);
            }
        }
    }

    /**
     * Build juz data from verse data
     * 
     * @return void
     */
    protected function buildJuz()
    {

        $jus_names = [
            22 => 'الأحزاب',
            23 => 'يس',
            24 => 'الزمر',
            25 => 'فصلت',
            26 => 'الأحقاف',
            27 => 'الذاريات',
            28 => 'قد سمع',
            29 => 'تبارك',
            30 => 'عمّ',
        ];

        $juzData = DB::table('quran_verses')
            ->select('juz_id')
            ->distinct()
            ->pluck('juz_id');

        foreach ($juzData as $juz) {
            // $juz = DB::table('quran_verses')->where('juz', $juz)->first();
            $first = DB::table('quran_verses')->where('juz_id', $juz)->first();
            $last = DB::table('quran_verses')->where('juz_id', $juz)->orderByDesc('id')->first();

            DB::table('quran_juz')->updateOrInsert(
                ['id' => $juz],
                [
                    'name' => $jus_names[$juz] ?? 'الجزء ' . $juz,
                    'start_surah_id' => $first->surah_id,
                    'start_aya' => $first->number,
                    'end_surah_id' => $last->surah_id,
                    'end_aya' => $last->number,
                ]
            );
        }
    }
}