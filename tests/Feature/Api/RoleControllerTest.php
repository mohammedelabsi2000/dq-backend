<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seedBaseData();
    }

    protected function seedBaseData(): void
    {
        $this->adminUser = User::factory()->create();
        $this->adminUser->givePermissionTo([
            'roles.show',
            'roles.create',
            'roles.update',
            'roles.delete',
        ]);

        $this->regularUser = User::factory()->create();
    }

    // ─── index ──────────────────────────────────────────────

    public function test_index_returns_roles()
    {
        Role::create(['name' => 'دور تجريبي', 'guard_name' => 'sanctum']);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/roles');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'دور تجريبي']);
    }

    public function test_index_requires_authentication()
    {
        $this->getJson('/api/roles')
            ->assertUnauthorized();
    }

    public function test_index_requires_permission()
    {
        $this->actingAs($this->regularUser)
            ->getJson('/api/roles')
            ->assertForbidden();
    }

    // ─── store ──────────────────────────────────────────────

    public function test_store_creates_role()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/roles', [
                'name' => 'دور جديد',
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'دور جديد']);

        $this->assertDatabaseHas('roles', ['name' => 'دور جديد']);
    }

    public function test_store_creates_role_with_permissions()
    {
        $permission = Permission::where('guard_name', 'sanctum')->first();

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/roles', [
                'name'      => 'دور بصلاحيات',
                'abilities' => [$permission->id],
            ]);

        $response->assertCreated();

        $role = Role::where('name', 'دور بصلاحيات')->first();
        $this->assertTrue($role->hasPermissionTo($permission->name));
    }

    public function test_store_creates_role_with_all_permissions()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/roles', [
                'name'     => 'مدير كامل',
                'give_all' => true,
            ]);

        $response->assertCreated();

        $role = Role::where('name', 'مدير كامل')->first();
        $this->assertEquals(
            Permission::count(),
            $role->permissions()->count()
        );
    }

    public function test_store_validation_requires_name()
    {
        $this->actingAs($this->adminUser)
            ->postJson('/api/roles', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_permission()
    {
        $this->actingAs($this->regularUser)
            ->postJson('/api/roles', ['name' => 'دور'])
            ->assertForbidden();
    }

    // ─── show ───────────────────────────────────────────────

    public function test_show_returns_role()
    {
        $role = Role::create(['name' => 'دور للعرض', 'guard_name' => 'sanctum']);

        $this->actingAs($this->adminUser)
            ->getJson("/api/roles/{$role->id}")
            ->assertOk()
            ->assertJsonFragment(['name' => 'دور للعرض']);
    }

    public function test_show_requires_permission()
    {
        $role = Role::create(['name' => 'دور', 'guard_name' => 'sanctum']);

        $this->actingAs($this->regularUser)
            ->getJson("/api/roles/{$role->id}")
            ->assertForbidden();
    }

    // ─── update ─────────────────────────────────────────────

    public function test_update_modifies_role()
    {
        $role = Role::create(['name' => 'دور قديم', 'guard_name' => 'sanctum']);

        $this->actingAs($this->adminUser)
            ->putJson("/api/roles/{$role->id}", [
                'name' => 'دور محدث',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'دور محدث']);

        $this->assertDatabaseHas('roles', ['name' => 'دور محدث']);
    }

    public function test_update_syncs_permissions()
    {
        $role       = Role::create(['name' => 'دور', 'guard_name' => 'sanctum']);
        $permission = Permission::where('guard_name', 'sanctum')->first();

        $this->actingAs($this->adminUser)
            ->putJson("/api/roles/{$role->id}", [
                'name'      => 'دور',
                'abilities' => [$permission->id],
            ])
            ->assertOk();

        $this->assertTrue($role->fresh()->hasPermissionTo($permission->name));
    }

    public function test_update_clears_permissions_when_none_given()
    {
        $role       = Role::create(['name' => 'دور', 'guard_name' => 'sanctum']);
        $permission = Permission::where('guard_name', 'sanctum')->first();
        $role->givePermissionTo($permission);

        $this->actingAs($this->adminUser)
            ->putJson("/api/roles/{$role->id}", [
                'name' => 'دور',
            ])
            ->assertOk();

        $this->assertEquals(0, $role->fresh()->permissions()->count());
    }

    public function test_update_requires_permission()
    {
        $role = Role::create(['name' => 'دور', 'guard_name' => 'sanctum']);

        $this->actingAs($this->regularUser)
            ->putJson("/api/roles/{$role->id}", ['name' => 'دور'])
            ->assertForbidden();
    }

    // ─── destroy ────────────────────────────────────────────

    public function test_destroy_deletes_role()
    {
        $role = Role::create(['name' => 'دور للحذف', 'guard_name' => 'sanctum']);

        $this->actingAs($this->adminUser)
            ->deleteJson("/api/roles/{$role->id}")
            ->assertOk();

        $this->assertDatabaseMissing('roles', ['name' => 'دور للحذف']);
    }

    public function test_destroy_requires_permission()
    {
        $role = Role::create(['name' => 'دور', 'guard_name' => 'sanctum']);

        $this->actingAs($this->regularUser)
            ->deleteJson("/api/roles/{$role->id}")
            ->assertForbidden();
    }

    // ─── abilities ──────────────────────────────────────────

    // ─── abilities ──────────────────────────────────────────

    public function test_abilities_returns_all_permissions()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/abilities');

        $response->assertOk();

        $count = Permission::where('guard_name', 'sanctum')->count();
        $this->assertCount($count, $response->json('data'));
    }

    public function test_abilities_requires_permission()
    {
        $this->actingAs($this->regularUser)
            ->getJson('/api/abilities')
            ->assertForbidden();
    }
}
