<?php

namespace Tests\Feature\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Database\Seeders\PermissionSeeder;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->policy = new UserPolicy();
    }

    // ─── viewAny ───────────────────────────────────────────

    public function test_user_with_permission_can_view_any()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.show');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_permission_cannot_view_any()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    // ─── view ───────────────────────────────────────────────

    public function test_user_with_permission_can_view_target()
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();
        $user->givePermissionTo('users.show');

        $this->assertTrue($this->policy->view($user, $target));
    }

    public function test_user_without_permission_cannot_view_target()
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();

        $this->assertFalse($this->policy->view($user, $target));
    }

    // ─── create ─────────────────────────────────────────────

    public function test_user_with_permission_can_create()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_permission_cannot_create()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->create($user));
    }

    // ─── update ─────────────────────────────────────────────

    public function test_user_with_permission_can_update_target()
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();
        $user->givePermissionTo('users.update');

        $this->assertTrue($this->policy->update($user, $target));
    }

    public function test_user_without_permission_cannot_update_target()
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();

        $this->assertFalse($this->policy->update($user, $target));
    }

    // ─── delete ─────────────────────────────────────────────

    public function test_user_with_permission_can_delete_target()
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();
        $user->givePermissionTo('users.delete');

        $this->assertTrue($this->policy->delete($user, $target));
    }

    public function test_user_without_permission_cannot_delete_target()
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();

        $this->assertFalse($this->policy->delete($user, $target));
    }
}
