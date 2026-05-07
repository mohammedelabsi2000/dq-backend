<?php

namespace Tests\Feature\Policies;

use App\Models\Branch;
use App\Models\Mosque;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use App\Policies\StudentPolicy;
use Database\Seeders\PermissionSeeder;
use Tests\TestCase;

class StudentPolicyTest extends TestCase
{
    protected StudentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->policy = new StudentPolicy();
    }

    protected function createStudent(): Student
    {
        $branch = Branch::factory()->create();
        $region = Region::factory()->create(['branch_id' => $branch->id]);
        $mosque = Mosque::factory()->create(['region_id' => $region->id]);

        return Student::factory()->create(['mosque_id' => $mosque->id]);
    }

    // ─── viewAny ───────────────────────────────────────────

    public function test_user_with_permission_can_view_any()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('students.show');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_permission_cannot_view_any()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->viewAny($user));
    }

    // ─── view ───────────────────────────────────────────────

    public function test_user_with_permission_can_view_student()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('students.show');
        $student = $this->createStudent();

        $this->assertTrue($this->policy->view($user, $student));
    }

    public function test_user_without_permission_cannot_view_student()
    {
        $user    = User::factory()->create();
        $student = $this->createStudent();

        $this->assertFalse($this->policy->view($user, $student));
    }

    // ─── create ─────────────────────────────────────────────

    public function test_user_with_permission_can_create()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('students.create');

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
        $user    = User::factory()->create();
        $user->givePermissionTo('students.update');
        $student = $this->createStudent();

        $this->assertTrue($this->policy->update($user, $student));
    }

    public function test_user_without_permission_cannot_update()
    {
        $user    = User::factory()->create();
        $student = $this->createStudent();

        $this->assertFalse($this->policy->update($user, $student));
    }

    // ─── delete ─────────────────────────────────────────────

    public function test_user_with_permission_can_delete()
    {
        $user    = User::factory()->create();
        $user->givePermissionTo('students.delete');
        $student = $this->createStudent();

        $this->assertTrue($this->policy->delete($user, $student));
    }

    public function test_user_without_permission_cannot_delete()
    {
        $user    = User::factory()->create();
        $student = $this->createStudent();

        $this->assertFalse($this->policy->delete($user, $student));
    }
}
