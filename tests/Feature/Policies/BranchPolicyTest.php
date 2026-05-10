<?php

namespace Tests\Feature\Policies;

use App\Models\Branch;
use App\Models\User;
use App\Policies\BranchPolicy;
use Database\Seeders\PermissionSeeder;
use Tests\TestCase;

class BranchPolicyTest extends TestCase
{

    protected BranchPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->policy = new BranchPolicy();
    }

    // ─── viewAny ───────────────────────────────────────────

    public function test_user_with_permission_can_view_any()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branches.show');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_permission_cannot_view_any()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    // ─── view ───────────────────────────────────────────────

    public function test_user_with_permission_can_view_branch()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branches.show');

        // BranchPolicy::view لا تأخذ $branch كـ parameter
        $this->assertTrue($this->policy->view($user));
    }

    public function test_user_without_permission_cannot_view_branch()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->view($user));
    }

    // ─── create ─────────────────────────────────────────────

    public function test_user_with_permission_can_create()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branches.create');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_permission_cannot_create()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->create($user));
    }

    // ─── update ─────────────────────────────────────────────

    public function test_user_with_permission_can_update()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branches.update');

        $this->assertTrue($this->policy->update($user));
    }

    public function test_user_without_permission_cannot_update()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->update($user));
    }

    // ─── delete ─────────────────────────────────────────────

    public function test_user_with_permission_can_delete()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branches.delete');

        $this->assertTrue($this->policy->delete($user));
    }

    public function test_user_without_permission_cannot_delete()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->delete($user));
    }
}
