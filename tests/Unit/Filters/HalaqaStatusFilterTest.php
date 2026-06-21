<?php

namespace Tests\Unit\Filters;

use App\Filters\HalaqaStatusFilter;
use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\Halaqa;
use App\Models\HalaqaStatus;
use Illuminate\Http\Request;
use Tests\TestCase;

class HalaqaStatusFilterTest extends TestCase
{
    private ?int $statusTypeId = null;
    private ?int $sponsorshipTypeId = null;

    public function assignConstantTypes()
    {
        $this->statusTypeId = (ConstantType::where('name', 'status_type')->first()
            ?? ConstantType::factory()->create(['name' => 'status_type']))->id;

        $this->sponsorshipTypeId = (ConstantType::where('name', 'sponsorship_type')->first()
            ?? ConstantType::factory()->create(['name' => 'sponsorship_type']))->id;
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->assignConstantTypes();

        // Ensure we start with no existing HalaqaStatus records
        HalaqaStatus::query()->delete();
    }

    /** @test */
    public function it_can_filter_by_halaqa_id()
    {
        $halaqa1 = Halaqa::factory()->create();
        $halaqa2 = Halaqa::factory()->create();

        HalaqaStatus::factory()->count(3)->create(['halaqa_id' => $halaqa1->id]);
        HalaqaStatus::factory()->count(2)->create(['halaqa_id' => $halaqa2->id]);

        $request = new Request(['halaqa_id' => $halaqa1->id]);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(3, $results);
        $this->assertEquals($halaqa1->id, $results->first()->halaqa_id);
    }

    /** @test */
    public function it_can_filter_by_status_type_id()
    {

        $statusType1 = Constant::factory()->create(['constant_type_id' => $this->statusTypeId]);
        $statusType2 = Constant::factory()->create(['constant_type_id' => $this->statusTypeId]);

        HalaqaStatus::factory()->count(3)->create(['status_type_id' => $statusType1->id]);
        HalaqaStatus::factory()->count(2)->create(['status_type_id' => $statusType2->id]);

        $request = new Request(['status_type_id' => $statusType1->id]);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(3, $results);
        $this->assertEquals($statusType1->id, $results->first()->status_type_id);
    }

    /** @test */
    public function it_can_filter_by_sponsorship_type_id()
    {
        $sponsorshipType1 = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);
        $sponsorshipType2 = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        HalaqaStatus::factory()->count(3)->create(['sponsorship_type_id' => $sponsorshipType1->id]);
        HalaqaStatus::factory()->count(2)->create(['sponsorship_type_id' => $sponsorshipType2->id]);

        $request = new Request(['sponsorship_type_id' => $sponsorshipType1->id]);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(3, $results);
        $this->assertEquals($sponsorshipType1->id, $results->first()->sponsorship_type_id);
    }

    /** @test */
    public function it_can_filter_by_from_date()
    {
        HalaqaStatus::factory()->create(['from_date' => '2024-01-01']);
        HalaqaStatus::factory()->create(['from_date' => '2024-02-01']);
        HalaqaStatus::factory()->create(['from_date' => '2024-03-01']);

        $request = new Request(['from_date' => '2024-02-01']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(2, $results);
        $this->assertEquals('2024-02-01', $results->first()->from_date->format('Y-m-d'));
    }

    /** @test */
    public function it_can_filter_by_to_date()
    {
        HalaqaStatus::factory()->create(['to_date' => '2024-01-31']);
        HalaqaStatus::factory()->create(['to_date' => '2024-02-29']);
        HalaqaStatus::factory()->create(['to_date' => '2024-03-31']);

        $request = new Request(['to_date' => '2024-02-29']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(2, $results);
        $this->assertEquals('2024-01-31', $results->first()->to_date->format('Y-m-d'));
    }

    /** @test */
    public function it_can_filter_by_active_only_true()
    {
        HalaqaStatus::factory()->create(['to_date' => '2024-01-31']); // Inactive
        HalaqaStatus::factory()->create(['to_date' => null]); // Active
        HalaqaStatus::factory()->create(['to_date' => now()->addMonth()]); // Active

        $request = new Request(['active_only' => 'true']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertTrue(
                is_null($result->to_date) || $result->to_date >= now()
            );
        }
    }

    /** @test */
    public function it_can_filter_by_active_only_false()
    {
        HalaqaStatus::factory()->create(['to_date' => '2024-01-31']); // Inactive
        HalaqaStatus::factory()->create(['to_date' => null]); // Active
        HalaqaStatus::factory()->create(['to_date' => now()->addMonth()]); // Active

        $request = new Request(['active_only' => 'false']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(3, $results); // Should return all records
    }

    /** @test */
    public function it_can_apply_multiple_filters()
    {
        $halaqa = Halaqa::factory()->create();
        $statusType = Constant::factory()->create(['constant_type_id' => $this->statusTypeId]);

        HalaqaStatus::factory()->create([
            'halaqa_id' => $halaqa->id,
            'status_type_id' => $statusType->id,
            'from_date' => '2024-02-01',
            'to_date' => null
        ]);

        HalaqaStatus::factory()->create([
            'halaqa_id' => $halaqa->id,
            'status_type_id' => $statusType->id,
            'from_date' => '2024-01-01',
            'to_date' => '2024-01-31'
        ]);

        HalaqaStatus::factory()->create([
            'halaqa_id' => Halaqa::factory()->create()->id,
            'status_type_id' => $statusType->id,
            'from_date' => '2024-02-01',
            'to_date' => null
        ]);

        $request = new Request([
            'halaqa_id' => $halaqa->id,
            'status_type_id' => $statusType->id,
            'from_date' => '2024-02-01',
            'active_only' => 'true'
        ]);

        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($halaqa->id, $results->first()->halaqa_id);
        $this->assertEquals($statusType->id, $results->first()->status_type_id);
        $this->assertEquals('2024-02-01', $results->first()->from_date->format('Y-m-d'));
        $this->assertNull($results->first()->to_date);
    }

    /** @test */
    public function it_returns_all_records_when_no_filters_applied()
    {
        HalaqaStatus::factory()->count(5)->create();

        $request = new Request();
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(5, $results);
    }

    /** @test */
    public function it_handles_empty_request_gracefully()
    {
        $request = new Request();
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);

        $this->assertInstanceOf(HalaqaStatusFilter::class, $filter);
        $results = $filter->apply();
        $this->assertNotNull($results);
    }

    /** @test */
    public function it_handles_invalid_halaqa_id()
    {
        HalaqaStatus::factory()->count(3)->create();

        $request = new Request(['halaqa_id' => 999]);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function it_handles_invalid_status_type_id()
    {
        HalaqaStatus::factory()->count(3)->create();

        $request = new Request(['status_type_id' => 999]);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function it_handles_invalid_sponsorship_type_id()
    {
        HalaqaStatus::factory()->count(3)->create();

        $request = new Request(['sponsorship_type_id' => 999]);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function it_handles_date_range_filters()
    {
        HalaqaStatus::factory()->create(['from_date' => '2024-01-01', 'to_date' => '2024-01-31']);
        HalaqaStatus::factory()->create(['from_date' => '2024-02-01', 'to_date' => '2024-02-29']);
        HalaqaStatus::factory()->create(['from_date' => '2024-03-01', 'to_date' => '2024-03-31']);

        $request = new Request([
            'from_date' => '2024-02-01',
            'to_date' => '2024-02-29'
        ]);

        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('2024-02-01', $results->first()->from_date->format('Y-m-d'));
        $this->assertEquals('2024-02-29', $results->first()->to_date->format('Y-m-d'));
    }

    /** @test */
    public function it_handles_null_to_date_in_active_only_filter()
    {
        HalaqaStatus::factory()->create(['to_date' => null]);
        HalaqaStatus::factory()->create(['to_date' => now()->subMonth()]); // Inactive

        $request = new Request(['active_only' => 'true']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(1, $results);
        $this->assertNull($results->first()->to_date);
    }

    /** @test */
    public function it_handles_future_to_date_in_active_only_filter()
    {
        HalaqaStatus::factory()->create(['to_date' => now()->addMonth()]);
        HalaqaStatus::factory()->create(['to_date' => now()->subMonth()]); // Inactive

        $request = new Request(['active_only' => 'true']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();

        $this->assertCount(1, $results);
        $this->assertGreaterThanOrEqual(now(), $results->first()->to_date);
    }

    /** @test */
    public function it_handles_string_boolean_values()
    {
        HalaqaStatus::factory()->create(['to_date' => null]);
        HalaqaStatus::factory()->create(['to_date' => now()->subMonth()]);

        // Test with "1"
        $request = new Request(['active_only' => '1']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();
        $this->assertCount(1, $results);

        // Test with "0"
        $request = new Request(['active_only' => '0']);
        $filter = new HalaqaStatusFilter(HalaqaStatus::query(), $request);
        $results = $filter->apply()->get();
        $this->assertCount(2, $results);
    }
}
