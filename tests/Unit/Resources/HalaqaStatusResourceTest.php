<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\HalaqaStatusResource;
use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\HalaqaStatus;
use Illuminate\Http\Request;
use Tests\TestCase;

class HalaqaStatusResourceTest extends TestCase
{
    private ?int $sponsorshipTypeId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->assignConstantTypes();
    }

    public function assignConstantTypes()
    {
        $this->sponsorshipTypeId = (ConstantType::where('name', 'sponsorship_type')->first()
            ?? ConstantType::factory()->create(['name' => 'sponsorship_type']))->id;
    }

    /** @test */
    public function it_transforms_halaqa_status_to_array()
    {
        $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        $halaqaStatus = HalaqaStatus::factory()->create([
            'sponsorship_type_id' => $sponsorshipType->id,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'notes' => 'Test notes'
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertEquals($halaqaStatus->id, $array['id']);
        $this->assertEquals('2024-01-01', $array['from_date']);
        $this->assertEquals('2024-12-31', $array['to_date']);
        $this->assertEquals('Test notes', $array['notes']);
    }

    /** @test */
    public function it_handles_null_sponsorship_type()
    {
        $halaqaStatus = HalaqaStatus::factory()->create([
            'sponsorship_type_id' => null,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'notes' => 'Test notes'
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertNull($array['sponsorship_type']);
    }

    /** @test */
    public function it_handles_null_to_date()
    {
        $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        $halaqaStatus = HalaqaStatus::factory()->create([
            'sponsorship_type_id' => $sponsorshipType->id,
            'from_date' => '2024-01-01',
            'to_date' => null,
            'notes' => 'Test notes'
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertNull($array['to_date']);
    }

    /** @test */
    public function it_handles_null_notes()
    {
        $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        $halaqaStatus = HalaqaStatus::factory()->create([
            'sponsorship_type_id' => $sponsorshipType->id,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'notes' => null
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertNull($array['notes']);
    }

    /** @test */
    public function it_transforms_sponsorship_type_correctly()
    {
        $sponsorshipType = Constant::factory()->create([
            'constant_type_id' => $this->sponsorshipTypeId,
            'name' => 'Full Sponsorship',
        ]);

        $halaqaStatus = HalaqaStatus::factory()->create([
            'sponsorship_type_id' => $sponsorshipType->id,
            'from_date' => '2024-01-01',
            'to_date' => null,
            'notes' => null
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertEquals($sponsorshipType->id, $array['sponsorship_type']['id']);
        $this->assertEquals('Full Sponsorship', $array['sponsorship_type']['name']);
    }

    /** @test */
    public function it_returns_all_required_fields()
    {
        $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        $halaqaStatus = HalaqaStatus::factory()->create([
            'sponsorship_type_id' => $sponsorshipType->id,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'notes' => 'Test notes'
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $expectedKeys = ['id', 'sponsorship_type', 'from_date', 'to_date', 'notes'];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    /** @test */
    public function it_can_be_used_in_collection()
    {
        $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        $halaqaStatuses = HalaqaStatus::factory()->count(3)->create([
            'sponsorship_type_id' => $sponsorshipType->id,
        ]);

        $collection = HalaqaStatusResource::collection($halaqaStatuses);
        $array = $collection->toArray(new Request());

        $this->assertCount(3, $array);

        foreach ($array as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('sponsorship_type', $item);
            $this->assertArrayHasKey('from_date', $item);
            $this->assertArrayHasKey('to_date', $item);
            $this->assertArrayHasKey('notes', $item);
        }
    }

    /** @test */
    public function it_handles_empty_halaqa_status()
    {
        $halaqaStatus = new HalaqaStatus();
        $halaqaStatus->id = 1;
        $halaqaStatus->from_date = '2024-01-01';
        $halaqaStatus->to_date = null;
        $halaqaStatus->notes = null;

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertEquals(1, $array['id']);
        $this->assertEquals('2024-01-01', $array['from_date']);
        $this->assertNull($array['to_date']);
        $this->assertNull($array['notes']);
        $this->assertNull($array['sponsorship_type']);
    }

    /** @test */
    public function it_preserves_date_format()
    {
        $halaqaStatus = HalaqaStatus::factory()->create([
            'from_date' => '2024-01-15',
            'to_date' => '2024-12-25',
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertEquals('2024-01-15', $array['from_date']);
        $this->assertEquals('2024-12-25', $array['to_date']);
    }

    /** @test */
    public function it_handles_long_notes()
    {
        $longNotes = str_repeat('This is a very long note. ', 50);

        $halaqaStatus = HalaqaStatus::factory()->create([
            'notes' => $longNotes
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertEquals($longNotes, $array['notes']);
    }

    /** @test */
    public function it_works_with_minimal_data()
    {
        $halaqaStatus = HalaqaStatus::factory()->create([
            'sponsorship_type_id' => null,
            'from_date' => '2024-01-01',
            'to_date' => null,
            'notes' => null
        ]);

        $resource = new HalaqaStatusResource($halaqaStatus);
        $array = $resource->toArray(new Request());

        $this->assertEquals($halaqaStatus->id, $array['id']);
        $this->assertEquals('2024-01-01', $array['from_date']);
        $this->assertNull($array['to_date']);
        $this->assertNull($array['notes']);
        $this->assertNull($array['sponsorship_type']);
    }
}
