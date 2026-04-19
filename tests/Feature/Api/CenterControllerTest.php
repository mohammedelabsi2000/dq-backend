<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Mosque;
use App\Models\Center;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CenterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Branch $branch;
    protected Region $region;
    protected Mosque $mosque;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    // ============================================================
    // 📋 index
    // ============================================================

    public function test_index_returns_centers()
    {
        Center::factory()->count(3)->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/centers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'total',
                'skip',
                'limit',
                'data' => [
                    '*' => ['id', 'name']
                ],
            ]);
    }

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/centers');

        $response->assertStatus(401);
    }

    public function test_index_requires_authorization()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/centers');

        $response->assertStatus(403);
    }

    public function test_index_search()
    {
        Center::factory()->create([
            'name'      => 'مركز النور',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);
        Center::factory()->create([
            'name'      => 'مركز الهدى',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/centers?search=النور');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('مركز النور'));
        $this->assertFalse($names->contains('مركز الهدى'));
    }

    public function test_index_filter_by_region()
    {
        $otherRegion = Region::factory()->create(['branch_id' => $this->branch->id]);
        $otherMosque = Mosque::factory()->create(['region_id' => $otherRegion->id]);

        Center::factory()->create([
            'name'      => 'مركز المنطقة الأولى',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);
        Center::factory()->create([
            'name'      => 'مركز المنطقة الثانية',
            'region_id' => $otherRegion->id,
            'mosque_id' => $otherMosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/centers?region_id={$this->region->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('مركز المنطقة الأولى'));
        $this->assertFalse($names->contains('مركز المنطقة الثانية'));
    }

    public function test_index_filter_by_mosque()
    {
        $otherMosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        Center::factory()->create([
            'name'      => 'مركز المسجد الأول',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);
        Center::factory()->create([
            'name'      => 'مركز المسجد الثاني',
            'region_id' => $this->region->id,
            'mosque_id' => $otherMosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/centers?mosque_id={$this->mosque->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('مركز المسجد الأول'));
        $this->assertFalse($names->contains('مركز المسجد الثاني'));
    }

    public function test_index_filter_by_branch()
    {
        $otherBranch = Branch::factory()->create();
        $otherRegion = Region::factory()->create(['branch_id' => $otherBranch->id]);
        $otherMosque = Mosque::factory()->create(['region_id' => $otherRegion->id]);

        Center::factory()->create([
            'name'      => 'مركز الفرع الأول',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);
        Center::factory()->create([
            'name'      => 'مركز الفرع الثاني',
            'region_id' => $otherRegion->id,
            'mosque_id' => $otherMosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/centers?branch_id={$this->branch->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('مركز الفرع الأول'));
        $this->assertFalse($names->contains('مركز الفرع الثاني'));
    }

    public function test_index_with_relations()
    {
        Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/centers?with_relations=true');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'region', 'mosque']
                ]
            ]);
    }

    // ============================================================
    // ➕ store
    // ============================================================

    public function test_store_creates_center()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/centers', [
                'name'      => 'مركز جديد',
                'region_id' => $this->region->id,
                'mosque_id' => $this->mosque->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'مركز جديد');

        $this->assertDatabaseHas('centers', [
            'name'      => 'مركز جديد',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);
    }

    public function test_store_validation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/centers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'region_id']);
    }

    // ============================================================
    // 👁️ show
    // ============================================================

    public function test_show_returns_center()
    {
        $center = Center::factory()->create([
            'name'      => 'مركز النور',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/centers/{$center->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'مركز النور')
            ->assertJsonStructure([
                'data' => ['id', 'name']
            ]);
    }

    public function test_show_requires_authorization()
    {
        $center = Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson("/api/centers/{$center->id}");

        $response->assertStatus(403);
    }

    public function test_show_with_mosque()
    {
        $center = Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/centers/{$center->id}?with_mosque=true");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'mosque']
            ]);
    }

    // ============================================================
    // ✏️ update
    // ============================================================

    public function test_update_modifies_center()
    {
        $center = Center::factory()->create([
            'name'      => 'الاسم القديم',
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/centers/{$center->id}", [
                'name'      => 'الاسم الجديد',
                'region_id' => $this->region->id,
                'mosque_id' => $this->mosque->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'الاسم الجديد');

        $this->assertDatabaseHas('centers', [
            'id'   => $center->id,
            'name' => 'الاسم الجديد',
        ]);
    }

    public function test_update_requires_authorization()
    {
        $center = Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson("/api/centers/{$center->id}", [
                'name'      => 'اسم جديد',
                'region_id' => $this->region->id,
            ]);

        $response->assertStatus(403);
    }

    // ============================================================
    // 🗑️ destroy
    // ============================================================

    public function test_destroy_deletes_center()
    {
        $center = Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/centers/{$center->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('centers', [
            'id'         => $center->id,
            'deleted_at' => null,
        ]);
    }

    public function test_destroy_requires_authorization()
    {
        $center = Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson("/api/centers/{$center->id}");

        $response->assertStatus(403);
    }

    // ============================================================
    // 🔧 Helpers
    // ============================================================

    protected function seedBaseData(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->branch = Branch::factory()->create();
        $this->region = Region::factory()->create(['branch_id' => $this->branch->id]);
        $this->mosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('مدير الدائرة');

        $this->regularUser = User::factory()->create();
    }
}
