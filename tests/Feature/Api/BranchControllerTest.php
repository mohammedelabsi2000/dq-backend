<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Region;

class BranchControllerTest extends TestCase
{

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    // ============================================================
    // 📋 index
    // ============================================================

    public function test_index_returns_branches()
    {
        Branch::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/branches');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'total',
                'skip',
                'limit',
                'data' => [
                    '*' => ['id', 'name', 'notes', 'regions_count', 'created_at']
                ],
            ]);
    }

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/branches');

        $response->assertStatus(401);
    }

    public function test_index_requires_authorization()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/branches');

        $response->assertStatus(403);
    }

    public function test_index_search()
    {
        Branch::factory()->create(['name' => 'فرع غرب غزة']);
        Branch::factory()->create(['name' => 'فرع شرق غزة']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/branches?search=غزة');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('فرع غرب غزة'));
        $this->assertTrue($names->contains('فرع شرق غزة'));
    }

    public function test_index_with_regions()
    {
        $branch = Branch::factory()->create();
        Region::factory()->count(2)->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/branches?with_regions=true');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'regions']
                ]
            ]);
    }

    // ============================================================
    // ➕ store
    // ============================================================

    public function test_store_creates_branch()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/branches', [
                'name' => 'فرع جديد',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'فرع جديد');

        $this->assertDatabaseHas('branches', ['name' => 'فرع جديد']);
    }

    public function test_store_validation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/branches', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // ============================================================
    // 👁️ show
    // ============================================================

    public function test_show_returns_branch()
    {
        $branch = Branch::factory()->create(['name' => 'فرع غرب غزة']);
        Region::factory()->count(2)->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/branches/{$branch->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'فرع غرب غزة')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'regions_count']
            ]);
    }

    public function test_show_requires_authorization()
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson("/api/branches/{$branch->id}");

        $response->assertStatus(403);
    }

    // ============================================================
    // ✏️ update
    // ============================================================

    public function test_update_modifies_branch()
    {
        $branch = Branch::factory()->create(['name' => 'فرع غرب غزة']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/branches/{$branch->id}", [
                'name' => 'فرع شرق غزة',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'فرع شرق غزة');

        $this->assertDatabaseHas('branches', [
            'id'   => $branch->id,
            'name' => 'فرع شرق غزة',
        ]);
    }

    // ============================================================
    // 🗑️ destroy
    // ============================================================

    public function test_destroy_deletes_branch()
    {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/branches/{$branch->id}");

        $response->assertStatus(200);

        // ✅ جرب assertDatabaseMissing أولاً لنشوف إذا اتحذف hard delete
        $this->assertDatabaseMissing('branches', [
            'id' => $branch->id,
            'deleted_at' => null,
        ]);
    }

    public function test_destroy_fails_with_regions()
    {
        $branch = Branch::factory()->create();
        Region::factory()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/branches/{$branch->id}");

        $response->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    // ============================================================
    // 🔧 Helpers
    // ============================================================

    protected function seedBaseData(): void
    {
        // ✅ شغّل الـ seeder — ينشئ كل الـ permissions الموجودة فعلاً
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // ✅ adminUser — أعطه الـ role كامل
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('مدير الدائرة');

        // ✅ regularUser — بدون أي role أو permission
        $this->regularUser = User::factory()->create();
    }
}
