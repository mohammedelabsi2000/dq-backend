<?php

namespace Tests\Unit\Traits;

use App\Models\Branch;
use App\Http\Traits\QueryFilterTrait;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryFilterTraitTest extends TestCase
{

    private object $controller;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->controller = new class {
    //         use QueryFilterTrait;
    //     };
    // }
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Branch::query()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->controller = new class {
            use QueryFilterTrait;
        };
    }

    // helper لتنظيف الـ request بين الـ tests
    private function freshRequest(array $params = []): void
    {
        request()->replace($params);
    }

    // ─── Pagination ─────────────────────────────────────────

    public function test_apply_filters_default_pagination()
    {
        $this->freshRequest();

        $result = $this->controller->applyFilters(Branch::query(), []);

        $this->assertEquals(0, $result['skip']);
        $this->assertEquals(10, $result['limit']);
    }

    public function test_apply_filters_custom_pagination()
    {
        $this->freshRequest(['skip' => 5, 'limit' => 3]);

        Branch::factory()->count(10)->create();

        $result = $this->controller->applyFilters(Branch::query(), []);
        $rows   = $result['query']->get();

        $this->assertEquals(5, $result['skip']);
        $this->assertEquals(3, $result['limit']);
        $this->assertCount(3, $rows);
    }

    public function test_apply_filters_no_limit()
    {

        Branch::query()->forceDelete();

        Branch::factory()->count(25)->create();
        $this->freshRequest();


        $result = $this->controller->applyFilters(Branch::query(), ['limit' => '*']);
        $rows   = $result['query']->get();

        $this->assertEquals('*', $result['limit']);
        $this->assertCount(25, $rows);
    }

    // ─── Search ─────────────────────────────────────────────

    public function test_apply_filters_search()
    {
        Branch::factory()->create(['name' => 'فرع الشمال']);
        Branch::factory()->create(['name' => 'فرع الجنوب']);
        Branch::factory()->create(['name' => 'مركز الوسط']);

        $this->freshRequest(['search' => 'فرع']);

        $result = $this->controller->applyFilters(Branch::query(), [
            'searchColumns' => ['name'],
        ]);

        $this->assertEquals(2, $result['count']);
        $this->assertCount(2, $result['query']->get());
    }

    public function test_apply_filters_search_multiple_columns()
    {
        Branch::factory()->create(['name' => 'فرع الشمال', 'notes' => 'ملاحظة عامة']);
        Branch::factory()->create(['name' => 'مركز الوسط', 'notes' => 'فرع رئيسي']);
        Branch::factory()->create(['name' => 'قسم الشرق',  'notes' => 'ملاحظة أخرى']);

        $this->freshRequest(['search' => 'فرع']);

        $result = $this->controller->applyFilters(Branch::query(), [
            'searchColumns' => ['name', 'notes'],
        ]);

        // يطابق "فرع الشمال" في name و "فرع رئيسي" في notes
        $this->assertEquals(2, $result['count']);
    }

    public function test_apply_filters_search_no_match()
    {
        Branch::factory()->create(['name' => 'فرع الشمال']);
        Branch::factory()->create(['name' => 'فرع الجنوب']);

        $this->freshRequest(['search' => 'كلمة غير موجودة']);

        $result = $this->controller->applyFilters(Branch::query(), [
            'searchColumns' => ['name'],
        ]);

        $this->assertEquals(0, $result['count']);
    }

    // ─── Ordering ───────────────────────────────────────────

    public function test_apply_filters_order_asc()
    {
        Branch::factory()->create(['name' => 'ج']);
        Branch::factory()->create(['name' => 'أ']);
        Branch::factory()->create(['name' => 'ب']);

        $this->freshRequest(['order_by' => 'asc']);

        $result = $this->controller->applyFilters(Branch::query(), [
            'orderColumn' => 'name',
            'limit'       => '*',
        ]);

        $names = $result['query']->get()->pluck('name')->toArray();

        $this->assertEquals(array_values($names), $names); // ترتيب صحيح
        $this->assertEquals($names, collect($names)->sort()->values()->toArray());
    }

    public function test_apply_filters_order_desc()
    {
        Branch::factory()->create(['name' => 'أ']);
        Branch::factory()->create(['name' => 'ب']);
        Branch::factory()->create(['name' => 'ج']);

        $this->freshRequest(['order_by' => 'desc']);

        $result = $this->controller->applyFilters(Branch::query(), [
            'orderColumn' => 'name',
            'limit'       => '*',
        ]);

        $names = $result['query']->get()->pluck('name')->toArray();

        $this->assertEquals($names, collect($names)->sortDesc()->values()->toArray());
    }

    public function test_apply_filters_order_asec_typo()
    {
        Branch::factory()->create(['name' => 'ج']);
        Branch::factory()->create(['name' => 'أ']);

        $this->freshRequest(['order_by' => 'asec']); // typo متعمد

        $result = $this->controller->applyFilters(Branch::query(), [
            'orderColumn' => 'name',
            'limit'       => '*',
        ]);

        $names = $result['query']->get()->pluck('name')->toArray();

        // asec → يُصحَّح إلى asc
        $this->assertEquals($names, collect($names)->sort()->values()->toArray());
    }

    // ─── Count ──────────────────────────────────────────────

    public function test_apply_filters_count_before_pagination()
    {

        Branch::query()->forceDelete();
        Branch::factory()->count(15)->create();

        $this->freshRequest(['skip' => 0, 'limit' => 5]);

        $result = $this->controller->applyFilters(Branch::query(), []);

        // count يعكس الكل (15) لا عدد الصفحة (5)
        $this->assertEquals(15, $result['count']);
        $this->assertCount(5, $result['query']->get());
    }

    public function test_apply_filters_count_with_search()
    {
        Branch::factory()->count(10)->create(['name' => 'فرع']);
        Branch::factory()->count(5)->create(['name'  => 'مركز']);

        $this->freshRequest(['search' => 'فرع', 'limit' => 3]);

        $result = $this->controller->applyFilters(Branch::query(), [
            'searchColumns' => ['name'],
        ]);

        // count = 10 (كل نتائج البحث)، rows = 3 (بعد الـ limit)
        $this->assertEquals(10, $result['count']);
        $this->assertCount(3, $result['query']->get());
    }

    // ─── applyFiltersA ──────────────────────────────────────

    public function test_apply_filters_a_returns_array()
    {
        Branch::query()->forceDelete();
        Branch::factory()->count(5)->create();

        $this->freshRequest();

        [$query, $skip, $limit, $count] = $this->controller->applyFiltersA(
            Branch::query(),
            []
        );

        $this->assertEquals(0, $skip);
        $this->assertEquals(10, $limit);
        $this->assertEquals(5, $count);
        $this->assertCount(5, $query->get());
    }

    public function test_apply_filters_a_matches_apply_filters()
    {
        Branch::factory()->count(3)->create();

        $this->freshRequest(['skip' => 1, 'limit' => 2]);

        $assoc   = $this->controller->applyFilters(Branch::query(), []);
        [$q, $s, $l, $c] = $this->controller->applyFiltersA(Branch::query(), []);

        $this->assertEquals($assoc['skip'],  $s);
        $this->assertEquals($assoc['limit'], $l);
        $this->assertEquals($assoc['count'], $c);
    }
}
