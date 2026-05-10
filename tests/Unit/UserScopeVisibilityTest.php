<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserScope;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Center;
use App\Models\Halaqa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserScopeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /** @test */
    public function branch_manager_can_only_see_users_in_their_branch()
    {
        // Create branches
        $branch1 = Branch::create(['name' => 'غرب غزة']);
        $branch2 = Branch::create(['name' => 'شمال غزة']);

        // Create regions
        $region1 = Region::create(['name' => 'الشاطئ', 'branch_id' => $branch1->id]);
        $region2 = Region::create(['name' => 'النصيرات', 'branch_id' => $branch2->id]);

        // Create centers
        $center1 = Center::create(['name' => 'مركز الشاطئ', 'region_id' => $region1->id]);
        $center2 = Center::create(['name' => 'مركز النصيرات', 'region_id' => $region2->id]);

        // Create users
        $branchManager = User::factory()->create();
        $userInBranch1 = User::factory()->create();
        $userInBranch2 = User::factory()->create();

        // Assign scopes
        UserScope::create(['user_id' => $branchManager->id, 'scope_type' => 'branch', 'scope_id' => $branch1->id]);
        UserScope::create(['user_id' => $userInBranch1->id, 'scope_type' => 'center', 'scope_id' => $center1->id]);
        UserScope::create(['user_id' => $userInBranch2->id, 'scope_type' => 'center', 'scope_id' => $center2->id]);

        // Test visibility
        $visibleUsers = User::visibleTo($branchManager)->get();

        $this->assertTrue($visibleUsers->contains($userInBranch1));
        $this->assertFalse($visibleUsers->contains($userInBranch2));
    }

    /** @test */
    public function region_manager_can_only_see_users_in_their_region()
    {
        // Create regions in same branch
        $branch = Branch::create(['name' => 'غرب غزة']);
        $region1 = Region::create(['name' => 'الشاطئ', 'branch_id' => $branch->id]);
        $region2 = Region::create(['name' => 'النصيرات', 'branch_id' => $branch->id]);

        // Create centers
        $center1 = Center::create(['name' => 'مركز الشاطئ', 'region_id' => $region1->id]);
        $center2 = Center::create(['name' => 'مركز النصيرات', 'region_id' => $region2->id]);

        // Create users
        $regionManager = User::factory()->create();
        $userInRegion1 = User::factory()->create();
        $userInRegion2 = User::factory()->create();

        // Assign scopes
        UserScope::create(['user_id' => $regionManager->id, 'scope_type' => 'region', 'scope_id' => $region1->id]);
        UserScope::create(['user_id' => $userInRegion1->id, 'scope_type' => 'center', 'scope_id' => $center1->id]);
        UserScope::create(['user_id' => $userInRegion2->id, 'scope_type' => 'center', 'scope_id' => $center2->id]);

        // Test visibility
        $visibleUsers = User::visibleTo($regionManager)->get();

        $this->assertTrue($visibleUsers->contains($userInRegion1));
        $this->assertFalse($visibleUsers->contains($userInRegion2));
    }

    /** @test */
    public function center_manager_can_only_see_users_in_their_center()
    {
        // Create center
        $branch = Branch::create(['name' => 'غرب غزة']);
        $region = Region::create(['name' => 'الشاطئ', 'branch_id' => $branch->id]);
        $center1 = Center::create(['name' => 'مركز الشاطئ', 'region_id' => $region->id]);
        $center2 = Center::create(['name' => 'مركز النصيرات', 'region_id' => $region->id]);

        // Create users
        $centerManager = User::factory()->create();
        $userInCenter1 = User::factory()->create();
        $userInCenter2 = User::factory()->create();

        // Assign scopes
        UserScope::create(['user_id' => $centerManager->id, 'scope_type' => 'center', 'scope_id' => $center1->id]);
        UserScope::create(['user_id' => $userInCenter1->id, 'scope_type' => 'center', 'scope_id' => $center1->id]);
        UserScope::create(['user_id' => $userInCenter2->id, 'scope_type' => 'center', 'scope_id' => $center2->id]);

        // Test visibility
        $visibleUsers = User::visibleTo($centerManager)->get();

        $this->assertTrue($visibleUsers->contains($userInCenter1));
        $this->assertFalse($visibleUsers->contains($userInCenter2));
    }

    /** @test */
    public function teacher_can_only_see_users_in_their_halaqa()
    {
        // Create halaqat
        $branch = Branch::create(['name' => 'غرب غزة']);
        $region = Region::create(['name' => 'الشاطئ', 'branch_id' => $branch->id]);
        $center = Center::create(['name' => 'مركز الشاطئ', 'region_id' => $region->id]);

        $halaqa1 = Halaqa::factory()->create(['name' => 'حلقة القرآن', 'reference_type' => 'center', 'reference_id' => $center->id]);
        $halaqa2 = Halaqa::factory()->create(['name' => 'حلقة التجويد', 'reference_type' => 'center', 'reference_id' => $center->id]);

        // Create users
        $teacher = User::factory()->create();
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();

        // Assign scopes
        UserScope::create(['user_id' => $teacher->id, 'scope_type' => 'halaqa', 'scope_id' => $halaqa1->id]);
        UserScope::create(['user_id' => $student1->id, 'scope_type' => 'halaqa', 'scope_id' => $halaqa1->id]);
        UserScope::create(['user_id' => $student2->id, 'scope_type' => 'halaqa', 'scope_id' => $halaqa2->id]);

        // Test visibility
        $visibleUsers = User::visibleTo($teacher)->get();

        $this->assertTrue($visibleUsers->contains($student1));
        $this->assertFalse($visibleUsers->contains($student2));
    }
}
