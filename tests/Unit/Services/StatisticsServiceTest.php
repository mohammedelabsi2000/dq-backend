<?php

namespace Tests\Unit\Services;

use App\Models\Branch;
use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Mosque;
use App\Models\Region;
use App\Models\Student;
use App\Services\StatisticsService;
use Database\Seeders\ConstantTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private StatisticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush(); // نبدأ كل test بـ cache نظيف
        $this->service = app(StatisticsService::class);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /** ينشئ hierarchy كاملة ويرجع mosque */
    private function createMosque(): Mosque
    {
        $branch = Branch::factory()->create();
        $region = Region::factory()->create(['branch_id' => $branch->id]);
        return Mosque::factory()->create(['region_id' => $region->id]);
    }

    // ─────────────────────────────────────────────
    // Tests
    // ─────────────────────────────────────────────

    /** @test */
    public function test_get_statistics_returns_correct_structure(): void
    {
        $result = $this->service->getStatistics();

        $expectedKeys = [
            'students_count',
            'halaqat_count',
            'branches_count',
            'regions_count',
            'mosques_count',
            'centers_count',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, "المفتاح [{$key}] غير موجود في النتيجة");
        }
    }

    /** @test */
    public function test_get_statistics_counts_are_correct(): void
    {
        $this->seedConstants();
        // ننشئ بيانات معروفة
        $branch = Branch::factory()->create();
        $region = Region::factory()->create(['branch_id' => $branch->id]);
        $mosque = Mosque::factory()->create(['region_id' => $region->id]);

        Branch::factory()->count(2)->create();
        Region::factory()->count(3)->create(['branch_id' => $branch->id]);
        Mosque::factory()->count(2)->create(['region_id' => $region->id]);
        Halaqa::factory()->count(4)->create();
        Center::factory()->count(2)->create();
        Student::factory()->count(5)->create(['mosque_id' => $mosque->id]);

        $result = $this->service->getStatistics();

        $this->assertEquals(Branch::count(),  $result['branches_count']);
        $this->assertEquals(Region::count(),  $result['regions_count']);
        $this->assertEquals(Mosque::count(),  $result['mosques_count']);
        $this->assertEquals(Halaqa::count(),  $result['halaqat_count']);
        $this->assertEquals(Center::count(),  $result['centers_count']);
        $this->assertEquals(Student::count(), $result['students_count']);
    }

    /** @test */
    // public function test_get_statistics_returns_zero_when_no_records(): void
    // {
    //     $result = $this->service->getStatistics();

    //     $this->assertEquals(0, $result['students_count']);
    //     $this->assertEquals(0, $result['branches_count']);
    //     $this->assertEquals(0, $result['halaqat_count']);
    // }

    /** @test */
    public function test_get_statistics_is_cached(): void
    {
        $this->seedConstants();
        // أول استدعاء → يحسب ويخزن
        $first = $this->service->getStatistics();

        // ننشئ بيانات جديدة بعد التخزين
        Branch::factory()->count(5)->create();

        // ثاني استدعاء → من الـ cache، لا يعكس البيانات الجديدة
        $second = $this->service->getStatistics();

        $this->assertEquals($first['branches_count'], $second['branches_count']);
    }

    /** @test */
    public function test_get_statistics_cache_key_is_statistics(): void
    {
        $this->assertFalse(Cache::has('statistics'));

        $this->service->getStatistics();

        $this->assertTrue(Cache::has('statistics'));
    }

    /** @test */
    // public function test_get_statistics_updates_after_cache_expires(): void
    // {
    //     // أول استدعاء → يخزن في الـ cache
    //     $first = $this->service->getStatistics();
    //     $this->assertEquals(0, $first['branches_count']);

    //     // نمسح الـ cache يدوياً (محاكاة انتهاء المدة)
    //     Cache::forget('statistics');

    //     // ننشئ بيانات جديدة
    //     Branch::factory()->count(3)->create();

    //     // ثالث استدعاء بعد انتهاء الـ cache → يعكس البيانات الجديدة
    //     $updated = $this->service->getStatistics();
    //     $this->assertEquals(3, $updated['branches_count']);
    // }

    /** @test */
    public function test_get_statistics_all_values_are_integers(): void
    {
        $result = $this->service->getStatistics();

        foreach ($result as $key => $value) {
            $this->assertIsInt($value, "القيمة [{$key}] يجب أن تكون integer");
        }
    }


    private function seedConstants(): void
    {
        $this->seed(ConstantTypeSeeder::class);
    }
}
