<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Mosque;

class RegionControllerTest extends TestCase
{
    protected User $adminUser;
    protected User $regularUser;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    // ============================================================
    // 📋 index
    // ============================================================

    public function test_index_returns_regions()
    {
        Region::factory()->count(3)->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/regions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'total',
                'skip',
                'limit',
                'data' => [
                    '*' => ['id', 'name', 'mosques_count']
                ],
            ]);
    }

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/regions');

        $response->assertStatus(401);
    }

    public function test_index_requires_authorization()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/regions');

        $response->assertStatus(403);
    }

    public function test_index_search()
    {
        Region::factory()->create(['name' => 'منطقة الرضوان', 'branch_id' => $this->branch->id]);
        Region::factory()->create(['name' => 'منطقة الشاطئ', 'branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/regions?search=الرضوان');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('منطقة الرضوان'));
        $this->assertFalse($names->contains('منطقة الشاطئ'));
    }

    public function test_index_filter_by_branch()
    {
        $otherBranch = Branch::factory()->create();
        Region::factory()->create(['name' => 'منطقة الفرع الأول', 'branch_id' => $this->branch->id]);
        Region::factory()->create(['name' => 'منطقة الفرع الثاني', 'branch_id' => $otherBranch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/regions?branch_id={$this->branch->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('منطقة الفرع الأول'));
        $this->assertFalse($names->contains('منطقة الفرع الثاني'));
    }

    public function test_index_with_branch()
    {
        Region::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/regions?with_branch=true');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'branch']
                ]
            ]);
    }

    // ============================================================
    // ➕ store
    // ============================================================

    public function test_store_creates_region()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/regions', [
                'name'      => 'منطقة جديدة',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'منطقة جديدة');

        $this->assertDatabaseHas('regions', [
            'name'      => 'منطقة جديدة',
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_store_validation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/regions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'branch_id']);
    }

    // ============================================================
    // 👁️ show
    // ============================================================

    public function test_show_returns_region()
    {
        $region = Region::factory()->create([
            'name'      => 'منطقة الرضوان',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/regions/{$region->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'منطقة الرضوان')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'mosques_count']
            ]);
    }

    public function test_show_requires_authorization()
    {
        $region = Region::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson("/api/regions/{$region->id}");

        $response->assertStatus(403);
    }

    public function test_show_with_branch()
    {
        $region = Region::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/regions/{$region->id}?with_branch=true");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'branch']
            ]);
    }

    // ============================================================
    // ✏️ update
    // ============================================================

    public function test_update_modifies_region()
    {
        $region = Region::factory()->create([
            'name'      => 'الاسم القديم',
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/regions/{$region->id}", [
                'name'      => 'الاسم الجديد',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'الاسم الجديد');

        $this->assertDatabaseHas('regions', [
            'id'   => $region->id,
            'name' => 'الاسم الجديد',
        ]);
    }

    // ============================================================
    // 🗑️ destroy
    // ============================================================

    public function test_destroy_deletes_region()
    {
        $region = Region::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/regions/{$region->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('regions', [
            'id'         => $region->id,
            'deleted_at' => null,
        ]);
    }

    public function test_destroy_fails_with_mosques()
    {
        $region = Region::factory()->create(['branch_id' => $this->branch->id]);
        Mosque::factory()->create(['region_id' => $region->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/regions/{$region->id}");

        $response->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    public function test_destroy_requires_authorization()
    {
        $region = Region::factory()->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson("/api/regions/{$region->id}");

        $response->assertStatus(403);
    }

    // ============================================================
    // 🔧 Helpers
    // ============================================================

    protected function seedBaseData(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->branch = Branch::factory()->create();

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('مدير الدائرة');

        $this->regularUser = User::factory()->create();
    }
}
