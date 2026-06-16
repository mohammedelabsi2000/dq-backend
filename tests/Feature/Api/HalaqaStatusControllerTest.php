<?php

namespace Tests\Feature\Api;

use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\Halaqa;
use App\Models\HalaqaStatus;
use Tests\TestCase;

class HalaqaStatusControllerTest extends TestCase
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
        $this->actingAsAdmin();
        $this->assignConstantTypes();
    }

    /** @test */
    public function it_can_list_halaqa_statuses()
    {
        HalaqaStatus::factory()->count(5)->create();

        $response = $this->getJson('/api/halaqa-statuses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'status_type',
                        'sponsorship_type',
                        'from_date',
                        'to_date',
                        'notes',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_halaqa_status()
    {
        $halaqa = Halaqa::factory()->create();

        $statusType = Constant::factory()->create(['constant_type_id' => $this->statusTypeId]);
        $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        $data = [
            'halaqa_id' => $halaqa->id,
            'status_type_id' => $statusType->id,
            'sponsorship_type_id' => $sponsorshipType->id,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'notes' => 'Test notes'
        ];

        $response = $this->postJson('/api/halaqa-statuses', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status_type',
                    'sponsorship_type',
                    'from_date',
                    'to_date',
                    'notes'
                ]
            ]);

        $this->assertDatabaseHas('halaqa_statuses', [
            'halaqa_id' => $halaqa->id,
            'status_type_id' => $statusType->id,
            'sponsorship_type_id' => $sponsorshipType->id,
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'notes' => 'Test notes'
        ]);
    }

    /** @test */
    public function it_can_show_a_halaqa_status()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        $response = $this->getJson("/api/halaqa-statuses/{$halaqaStatus->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'data' => [
                    'id',
                    'status_type',
                    'sponsorship_type',
                    'from_date',
                    'to_date',
                    'notes'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $halaqaStatus->id,
                    'from_date' => $halaqaStatus->from_date,
                    'to_date' => $halaqaStatus->to_date,
                    'notes' => $halaqaStatus->notes
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_halaqa_status()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        $newStatusType = Constant::factory()->create([
            'constant_type_id' => $this->statusTypeId
        ]);
        $data = [
            'status_type_id' => $newStatusType->id,
            'to_date' => '2024-06-30',
            'notes' => 'Updated notes',
        ];

        $response = $this->putJson("/api/halaqa-statuses/{$halaqaStatus->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status_type',
                    'sponsorship_type',
                    'from_date',
                    'to_date',
                    'notes'
                ]
            ]);

        $this->assertDatabaseHas('halaqa_statuses', [
            'id' => $halaqaStatus->id,
            'status_type_id' => $newStatusType->id,
            'to_date' => '2024-06-30',
            'notes' => 'Updated notes'
        ]);
    }

    /** @test */
    public function it_can_delete_a_halaqa_status()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        $response = $this->deleteJson("/api/halaqa-statuses/{$halaqaStatus->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSoftDeleted('halaqa_statuses', ['id' => $halaqaStatus->id]);
    }

    /** @test */
    public function it_filters_halaqa_statuses_by_halaqa_id()
    {
        $halaqa1 = Halaqa::factory()->create();
        $halaqa2 = Halaqa::factory()->create();

        HalaqaStatus::factory()->count(3)->create(['halaqa_id' => $halaqa1->id]);
        HalaqaStatus::factory()->count(2)->create(['halaqa_id' => $halaqa2->id]);

        $response = $this->getJson("/api/halaqa-statuses?halaqa_id={$halaqa1->id}");

        $response->assertStatus(200);

        $this->assertEquals(3, $response->json('total'));
    }

    /** @test */
    public function it_filters_halaqa_statuses_by_status_type_id()
    {

        $statusType1 = Constant::factory()->create(['constant_type_id' => $this->statusTypeId]);
        $statusType2 = Constant::factory()->create(['constant_type_id' => $this->statusTypeId]);

        HalaqaStatus::factory()->count(3)->create(['status_type_id' => $statusType1->id]);
        HalaqaStatus::factory()->count(2)->create(['status_type_id' => $statusType2->id]);

        $response = $this->getJson("/api/halaqa-statuses?status_type_id={$statusType1->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /** @test */
    public function it_filters_halaqa_statuses_by_sponsorship_type_id()
    {

        $sponsorshipType1 = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);
        $sponsorshipType2 = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

        HalaqaStatus::factory()->count(3)->create(['sponsorship_type_id' => $sponsorshipType1->id]);
        HalaqaStatus::factory()->count(2)->create(['sponsorship_type_id' => $sponsorshipType2->id]);

        $response = $this->getJson("/api/halaqa-statuses?sponsorship_type_id={$sponsorshipType1->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /** @test */
    public function it_filters_halaqa_statuses_by_from_date()
    {
        HalaqaStatus::query()->delete();

        HalaqaStatus::factory()->create(['from_date' => '2024-01-01']);
        HalaqaStatus::factory()->create(['from_date' => '2024-02-01']);
        HalaqaStatus::factory()->create(['from_date' => '2024-03-01']);

        $response = $this->getJson('/api/halaqa-statuses?from_date=2024-02-01');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('total'));
    }

    /** @test */
    public function it_filters_halaqa_statuses_by_to_date()
    {
        HalaqaStatus::query()->delete();

        HalaqaStatus::factory()->create(['to_date' => '2024-01-31']);
        HalaqaStatus::factory()->create(['to_date' => '2024-02-29']);
        HalaqaStatus::factory()->create(['to_date' => '2024-03-31']);

        $response = $this->getJson('/api/halaqa-statuses?to_date=2024-02-29');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('total'));
    }

    /** @test */
    public function it_filters_halaqa_statuses_by_active_only()
    {
        HalaqaStatus::query()->delete();

        HalaqaStatus::factory()->create(['to_date' => '2024-01-31']); // Inactive
        HalaqaStatus::factory()->create(['to_date' => null]); // Active
        HalaqaStatus::factory()->create(['to_date' => now()->addMonth()]); // Active

        $response = $this->getJson('/api/halaqa-statuses?active_only=true');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('total'));
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        $response = $this->postJson('/api/halaqa-statuses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['halaqa_id', 'from_date']);
    }

    /** @test */
    public function it_validates_halaqa_id_exists_when_creating()
    {
        $response = $this->postJson('/api/halaqa-statuses', [
            'halaqa_id' => 999,
            'from_date' => '2024-01-01'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['halaqa_id']);
    }

    /** @test */
    public function it_validates_to_date_is_after_from_date_when_creating()
    {
        $response = $this->postJson('/api/halaqa-statuses', [
            'halaqa_id' => Halaqa::factory()->create()->id,
            'from_date' => '2024-06-01',
            'to_date' => '2024-05-31'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_date']);
    }

    /** @test */
    public function it_validates_halaqa_id_exists_when_updating()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        $response = $this->putJson("/api/halaqa-statuses/{$halaqaStatus->id}", [
            'halaqa_id' => 999,
            'from_date' => '2024-01-01'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['halaqa_id']);
    }

    /** @test */
    public function it_validates_to_date_is_after_from_date_when_updating()
    {
        $halaqaStatus = HalaqaStatus::factory()->create();

        $response = $this->putJson("/api/halaqa-statuses/{$halaqaStatus->id}", [
            'halaqa_id' => $halaqaStatus->halaqa_id,
            'from_date' => '2024-06-01',
            'to_date' => '2024-05-31'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_date']);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_halaqa_status()
    {
        $response = $this->getJson('/api/halaqa-statuses/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_halaqa_status()
    {
        $response = $this->putJson('/api/halaqa-statuses/999', [
            'halaqa_id' => Halaqa::factory()->create()->id,
            'from_date' => '2024-01-01'
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_halaqa_status()
    {
        $response = $this->deleteJson('/api/halaqa-statuses/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_halaqa_status_with_minimal_data()
    {
        $halaqa = Halaqa::factory()->create();

        $data = [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01'
        ];

        $response = $this->postJson('/api/halaqa-statuses', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('halaqa_statuses', [
            'halaqa_id' => $halaqa->id,
            'from_date' => '2024-01-01',
            'status_type_id' => null,
            'sponsorship_type_id' => null,
            'to_date' => null,
            'notes' => null
        ]);
    }

    /** @test */
    // public function it_can_create_halaqa_status_with_all_optional_fields()
    // {
    //     $halaqa = Halaqa::factory()->create();


    //     $statusType = Constant::factory()->create(['constant_type_id' => $this->statusTypeId]);
    //     $sponsorshipType = Constant::factory()->create(['constant_type_id' => $this->sponsorshipTypeId]);

    //     $data = [
    //         'halaqa_id' => $halaqa->id,
    //         'status_type_id' => $statusType->id,
    //         'sponsorship_type_id' => $sponsorshipType->id,
    //         'from_date' => '2024-01-01',
    //         'to_date' => '2024-12-31',
    //         'notes' => 'Comprehensive test notes with detailed information'
    //     ];

    //     $response = $this->postJson('/api/halaqa-statuses', $data);

    //     $response->assertStatus(201);
    //     $this->assertDatabaseHas('halaqa_statuses', $data);
    // }
}
