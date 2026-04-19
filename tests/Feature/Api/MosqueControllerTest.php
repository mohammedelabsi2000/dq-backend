<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Mosque;
use App\Models\Center;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MosqueControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Branch $branch;
    protected Region $region;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    // ============================================================
    // 📋 index
    // ============================================================

    public function test_index_returns_mosques()
    {
        Mosque::factory()->count(3)->create(['region_id' => $this->region->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/mosques');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'total',
                'skip',
                'limit',
                'data' => [
                    '*' => ['id', 'name', 'centers_count']
                ],
            ]);
    }

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/mosques');

        $response->assertStatus(401);
    }

    public function test_index_requires_authorization()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/mosques');

        $response->assertStatus(403);
    }

    public function test_index_search()
    {
        Mosque::factory()->create(['name' => 'مسجد ابي بكر', 'region_id' => $this->region->id]);
        Mosque::factory()->create(['name' => 'مسجد اسيا', 'region_id' => $this->region->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/mosques?search=ابي');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('مسجد ابي بكر'));
        $this->assertFalse($names->contains('مسجد اسيا'));
    }

    public function test_index_filter_by_region()
    {
        $otherRegion = Region::factory()->create(['branch_id' => $this->branch->id]);
        Mosque::factory()->create(['name' => 'مسجد المنطقة الأولى', 'region_id' => $this->region->id]);
        Mosque::factory()->create(['name' => 'مسجد المنطقة الثانية', 'region_id' => $otherRegion->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/mosques?region_id={$this->region->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('مسجد المنطقة الأولى'));
        $this->assertFalse($names->contains('مسجد المنطقة الثانية'));
    }

    public function test_index_filter_by_branch()
    {
        $otherBranch = Branch::factory()->create();
        $otherRegion = Region::factory()->create(['branch_id' => $otherBranch->id]);
        Mosque::factory()->create(['name' => 'مسجد الفرع الأول', 'region_id' => $this->region->id]);
        Mosque::factory()->create(['name' => 'مسجد الفرع الثاني', 'region_id' => $otherRegion->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/mosques?branch_id={$this->branch->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('مسجد الفرع الأول'));
        $this->assertFalse($names->contains('مسجد الفرع الثاني'));
    }

    // ============================================================
    // ➕ store
    // ============================================================

    public function test_store_creates_mosque()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/mosques', [
                'name'      => 'مسجد جديد',
                'region_id' => $this->region->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.mosque.name', 'مسجد جديد');

        $this->assertDatabaseHas('mosques', [
            'name'      => 'مسجد جديد',
            'region_id' => $this->region->id,
        ]);
    }

    public function test_store_creates_mosque_with_center()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/mosques', [
                'name'          => 'مسجد مع مركز',
                'region_id'     => $this->region->id,
                'create_center' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['mosque', 'center']
            ]);

        $this->assertDatabaseHas('mosques', ['name' => 'مسجد مع مركز']);
        $this->assertDatabaseHas('centers', [
            'name'      => 'مسجد مع مركز',
            'region_id' => $this->region->id,
        ]);
    }

    public function test_store_validation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/mosques', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'region_id']);
    }

    // ============================================================
    // 👁️ show
    // ============================================================

    public function test_show_returns_mosque()
    {
        $mosque = Mosque::factory()->create([
            'name'      => 'مسجد الأقصى',
            'region_id' => $this->region->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/mosques/{$mosque->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'مسجد الأقصى')
            ->assertJsonStructure([
                'data' => ['id', 'name']
            ]);
    }

    public function test_show_requires_authorization()
    {
        $mosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson("/api/mosques/{$mosque->id}");

        $response->assertStatus(403);
    }

    public function test_show_with_region()
    {
        $mosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/mosques/{$mosque->id}?with_region=true");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'region']
            ]);
    }

    // ============================================================
    // ✏️ update
    // ============================================================

    public function test_update_modifies_mosque()
    {
        $mosque = Mosque::factory()->create([
            'name'      => 'الاسم القديم',
            'region_id' => $this->region->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/mosques/{$mosque->id}", [
                'name'      => 'الاسم الجديد',
                'region_id' => $this->region->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.mosque.name', 'الاسم الجديد');

        $this->assertDatabaseHas('mosques', [
            'id'   => $mosque->id,
            'name' => 'الاسم الجديد',
        ]);
    }

    public function test_update_creates_center_when_requested()
    {
        $mosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/mosques/{$mosque->id}", [
                'name'          => $mosque->name,
                'region_id'     => $this->region->id,
                'create_center' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['mosque', 'center']
            ]);

        $this->assertDatabaseHas('centers', [
            'mosque_id' => $mosque->id,
        ]);
    }

    public function test_update_requires_authorization()
    {
        $mosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson("/api/mosques/{$mosque->id}", [
                'name'      => 'اسم جديد',
                'region_id' => $this->region->id,
            ]);

        $response->assertStatus(403);
    }

    // ============================================================
    // 🗑️ destroy
    // ============================================================

    public function test_destroy_deletes_mosque()
    {
        $mosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/mosques/{$mosque->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('mosques', [
            'id'         => $mosque->id,
            'deleted_at' => null,
        ]);
    }

    public function test_destroy_fails_with_centers()
    {
        $mosque = Mosque::factory()->create(['region_id' => $this->region->id]);
        Center::factory()->create([
            'mosque_id' => $mosque->id,
            'region_id' => $this->region->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/mosques/{$mosque->id}");

        $response->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('mosques', ['id' => $mosque->id]);
    }

    public function test_destroy_requires_authorization()
    {
        $mosque = Mosque::factory()->create(['region_id' => $this->region->id]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson("/api/mosques/{$mosque->id}");

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

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('مدير الدائرة');

        $this->regularUser = User::factory()->create();
    }
}
