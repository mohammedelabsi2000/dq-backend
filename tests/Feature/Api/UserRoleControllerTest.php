<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected User $targetUser;

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
            'users.roles.update',
            'roles.update',
            'roles.delete',
        ]);

        $this->regularUser = User::factory()->create();
        $this->targetUser  = User::factory()->create();
    }

    // ─── index ──────────────────────────────────────────────

    public function test_index_returns_user_roles_and_permissions()
    {
        $role = Role::create(['name' => 'محرر', 'guard_name' => 'sanctum']);
        $this->targetUser->assignRole($role);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/users/{$this->targetUser->id}/roles");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['roles', 'permissions', 'scopes'],
            ])
            ->assertJsonFragment(['محرر']);
    }

    public function test_index_requires_authentication()
    {
        $this->getJson("/api/users/{$this->targetUser->id}/roles")
            ->assertUnauthorized();
    }

    public function test_index_requires_permission()
    {
        $this->actingAs($this->regularUser)
            ->getJson("/api/users/{$this->targetUser->id}/roles")
            ->assertForbidden();
    }

    // ─── assignRoles ────────────────────────────────────────

    public function test_assign_roles_syncs_user_roles()
    {
        $role = Role::create(['name' => 'محرر', 'guard_name' => 'sanctum']);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$role->id],
            ]);

        $response->assertCreated();
        $this->assertTrue($this->targetUser->fresh()->hasRole('محرر'));
    }

    public function test_assign_roles_replaces_existing_roles()
    {
        $roleA = Role::create(['name' => 'دور أ', 'guard_name' => 'sanctum']);
        $roleB = Role::create(['name' => 'دور ب', 'guard_name' => 'sanctum']);
        $this->targetUser->assignRole($roleA);

        $this->actingAs($this->adminUser)
            ->postJson("/api/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$roleB->id],
            ])
            ->assertCreated();

        $freshUser = $this->targetUser->fresh();
        $this->assertFalse($freshUser->hasRole('دور أ'));
        $this->assertTrue($freshUser->hasRole('دور ب'));
    }

    public function test_assign_roles_with_scopes()
    {
        $role = Role::create(['name' => 'مشرف', 'guard_name' => 'sanctum']);

        $this->actingAs($this->adminUser)
            ->postJson("/api/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$role->id],
                'scopes'   => [
                    ['type' => 'branch', 'id' => 1],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('user_scopes', [
            'user_id'    => $this->targetUser->id,
            'scope_type' => 'branch',
            'scope_id'   => 1,
        ]);
    }

    public function test_assign_roles_requires_permission()
    {
        $role = Role::create(['name' => 'دور', 'guard_name' => 'sanctum']);

        $this->actingAs($this->regularUser)
            ->postJson("/api/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$role->id],
            ])
            ->assertForbidden();
    }

    public function test_assign_roles_validation_requires_role_ids()
    {
        $this->actingAs($this->adminUser)
            ->postJson("/api/users/{$this->targetUser->id}/roles", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role_ids']);
    }

    // ─── removeRoles ────────────────────────────────────────

    public function test_remove_roles_clears_all_user_roles()
    {
        $role = Role::create(['name' => 'مشرف', 'guard_name' => 'sanctum']);
        $this->targetUser->assignRole($role);

        $this->actingAs($this->adminUser)
            ->deleteJson("/api/users/{$this->targetUser->id}/roles")
            ->assertOk();

        $this->assertCount(0, $this->targetUser->fresh()->roles);
    }

    public function test_remove_roles_requires_permission()
    {
        $this->actingAs($this->regularUser)
            ->deleteJson("/api/users/{$this->targetUser->id}/roles")
            ->assertForbidden();
    }

    // ─── assignScopes ───────────────────────────────────────

    public function test_assign_scopes_syncs_user_scopes()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/users/{$this->targetUser->id}/scopes", [
                'scopes' => [
                    ['type' => 'branch', 'id' => 1],
                    ['type' => 'region', 'id' => 2],
                ],
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('user_scopes', [
            'user_id'    => $this->targetUser->id,
            'scope_type' => 'branch',
            'scope_id'   => 1,
        ]);

        $this->assertDatabaseHas('user_scopes', [
            'user_id'    => $this->targetUser->id,
            'scope_type' => 'region',
            'scope_id'   => 2,
        ]);
    }

    public function test_assign_scopes_validation_requires_scopes()
    {
        $this->actingAs($this->adminUser)
            ->postJson("/api/users/{$this->targetUser->id}/scopes", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['scopes']);
    }

    // ─── removeScopes ───────────────────────────────────────

    public function test_remove_scopes_clears_all_user_scopes()
    {
        $this->targetUser->syncScopes([
            ['type' => 'branch', 'id' => 1],
        ]);

        $this->actingAs($this->adminUser)
            ->deleteJson("/api/users/{$this->targetUser->id}/scopes")
            ->assertOk();

        $this->assertDatabaseMissing('user_scopes', [
            'user_id' => $this->targetUser->id,
        ]);
    }

    public function test_remove_scopes_requires_permission()
    {
        $this->actingAs($this->regularUser)
            ->deleteJson("/api/users/{$this->targetUser->id}/scopes")
            ->assertForbidden();
    }
}
