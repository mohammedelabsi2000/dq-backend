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
        $branch = Branch::factory()->create();

        $this->assertTrue($this->policy->view($user, $branch));
    }

    public function test_user_without_permission_cannot_view_branch()
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();

        $this->assertFalse($this->policy->view($user, $branch));
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
        $branch = Branch::factory()->create();

        $this->assertTrue($this->policy->update($user, $branch));
    }

    public function test_user_without_permission_cannot_update()
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();

        $this->assertFalse($this->policy->update($user, $branch));
    }

    // ─── delete ─────────────────────────────────────────────

    public function test_user_with_permission_can_delete()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('branches.delete');
        $branch = Branch::factory()->create();

        $this->assertTrue($this->policy->delete($user, $branch));
    }

    public function test_user_without_permission_cannot_delete()
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();

        $this->assertFalse($this->policy->delete($user, $branch));
    }
}
