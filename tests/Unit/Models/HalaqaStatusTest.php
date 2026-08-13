<?php

namespace Tests\Unit\Models;

use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\Halaqa;
use App\Models\HalaqaStatus;
use Tests\TestCase;

class HalaqaStatusTest extends TestCase
{
    private ?int $sponsorshipTypeId = null;

    public function assignConstantTypes()
    {
        $this->sponsorshipTypeId = (ConstantType::where('name', 'sponsorship_type')->first()
            ?? ConstantType::factory()->create(['name' => 'sponsorship_type']))->id;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->assignConstantTypes();
    }

    /** @test */
    public function it_can_create_a_halaqa_status()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        $this->assertInstanceOf(HalaqaStatus::class, $halaqaStatus);
        $this->assertDatabaseHas('halaqa_statuses', [
            'id' => $halaqaStatus->id,
            'halaqa_id' => $halaqaStatus->halaqa_id,
            'from_date' => $halaqaStatus->from_date,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_halaqa()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        $this->assertInstanceOf(Halaqa::class, $halaqaStatus->halaqa);
        $this->assertEquals($halaqaStatus->halaqa_id, $halaqaStatus->halaqa->id);
    }

    /** @test */
    public function it_belongs_to_sponsorship_type_constant()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        if ($halaqaStatus->sponsorship_type_id) {
            $this->assertInstanceOf(Constant::class, $halaqaStatus->sponsorshipType);
            $this->assertEquals($halaqaStatus->sponsorship_type_id, $halaqaStatus->sponsorshipType->id);
        } else {
            $this->assertNull($halaqaStatus->sponsorshipType);
        }
    }

    /** @test */
    public function it_can_have_null_sponsorship_type_id()
    {
        $halaqaStatus = HalaqaStatus::factory()->create(['sponsorship_type_id' => null]);

        $this->assertNull($halaqaStatus->sponsorship_type_id);
        $this->assertNull($halaqaStatus->sponsorshipType);
    }

    /** @test */
    public function it_can_have_null_to_date()
    {
        $halaqaStatus = HalaqaStatus::factory()->create(['to_date' => null]);

        $this->assertNull($halaqaStatus->to_date);
    }

    /** @test */
    public function it_can_have_null_notes()
    {
        $halaqaStatus = HalaqaStatus::factory()->create(['notes' => null]);

        $this->assertNull($halaqaStatus->notes);
    }

    /** @test */
    public function it_uses_guarded_property()
    {
        $halaqaStatus = new HalaqaStatus();

        $this->assertEmpty($halaqaStatus->getGuarded());
    }

    /** @test */
    public function it_can_be_created_with_specific_dates()
    {
        $fromDate = '2024-01-01';
        $toDate = '2024-12-31';

        $halaqaStatus = HalaqaStatus::factory()->create([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        $this->assertEquals($fromDate, $halaqaStatus->from_date->format('Y-m-d'));
        $this->assertEquals($toDate, $halaqaStatus->to_date->format('Y-m-d'));
    }

    /** @test */
    public function it_can_be_created_with_notes()
    {
        $notes = 'Test notes for halaqa status';

        $halaqaStatus = HalaqaStatus::factory()->create(['notes' => $notes]);

        $this->assertEquals($notes, $halaqaStatus->notes);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $halaqaStatus = new HalaqaStatus();

        $this->assertEquals('halaqa_statuses', $halaqaStatus->getTable());
    }

    /** @test */
    public function it_has_correct_primary_key()
    {
        $halaqaStatus = new HalaqaStatus();

        $this->assertEquals('id', $halaqaStatus->getKeyName());
    }

    /** @test */
    public function it_uses_timestamps()
    {
        $halaqaStatus = new HalaqaStatus();

        $this->assertTrue($halaqaStatus->usesTimestamps());
    }

    /** @test */
    public function it_can_scope_by_halaqa()
    {
        $halaqa = Halaqa::factory()->create();
        $otherHalaqa = Halaqa::factory()->create();

        $halaqaStatus1 = HalaqaStatus::factory()->create(['halaqa_id' => $halaqa->id]);
        $halaqaStatus2 = HalaqaStatus::factory()->create(['halaqa_id' => $halaqa->id]);
        $halaqaStatus3 = HalaqaStatus::factory()->create(['halaqa_id' => $otherHalaqa->id]);

        $halaqaStatuses = HalaqaStatus::where('halaqa_id', $halaqa->id)->get();

        $this->assertCount(2, $halaqaStatuses);
        $this->assertTrue($halaqaStatuses->contains($halaqaStatus1));
        $this->assertTrue($halaqaStatuses->contains($halaqaStatus2));
        $this->assertFalse($halaqaStatuses->contains($halaqaStatus3));
    }

    /** @test */
    public function it_can_scope_by_sponsorship_type()
    {

        $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);
        $otherSponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        $halaqaStatus1 = HalaqaStatus::factory()->create(['sponsorship_type_id' => $sponsorshipType->id]);
        $halaqaStatus2 = HalaqaStatus::factory()->create(['sponsorship_type_id' => $sponsorshipType->id]);
        $halaqaStatus3 = HalaqaStatus::factory()->create(['sponsorship_type_id' => $otherSponsorshipType->id]);

        $halaqaStatuses = HalaqaStatus::where('sponsorship_type_id', $sponsorshipType->id)->get();

        $this->assertCount(2, $halaqaStatuses);
        $this->assertTrue($halaqaStatuses->contains($halaqaStatus1));
        $this->assertTrue($halaqaStatuses->contains($halaqaStatus2));
        $this->assertFalse($halaqaStatuses->contains($halaqaStatus3));
    }
}
