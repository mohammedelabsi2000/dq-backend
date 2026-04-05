<?php

namespace Tests\Feature;

use App\Models\Halaqa;
use App\Models\Role;
use App\Models\User;
use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalaqaSupervisorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test by seeding required data.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic required data
        $this->seed(\Database\Seeders\ConstantTypeSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /**
     * Test that halaqa supervisor is correctly retrieved based on role_user permissions.
     */
    public function test_halaqa_supervisor_retrieval()
    {
        // Get a constant type for halaqa type
        $constantType = ConstantType::where('name', 'نوع الحلقة')->first();
        $this->assertNotNull($constantType, 'Constant type for halaqa should exist');

        // Create a constant for halaqa type
        $type = Constant::create([
            'name' => 'حلقة قرآن',
            'constant_type_id' => $constantType->id,
        ]);

        // Create a halaqa manually with required fields
        $halaqa = Halaqa::create([
            'name' => 'Test Halaqa',
            'location' => 'Test Location',
            'description' => 'Test Description',
            'reference_type' => 'user', // Using user as reference for testing
            'reference_id' => 1,
            'type_id' => $type->id,
        ]);

        // Create a "محفظ" role if not exists
        $supervisorRole = Role::firstOrCreate(['name' => 'محفظ']);

        // Create a user to be the supervisor
        $supervisor = User::create([
            'fName' => 'محمد',
            'family' => 'أحمد',
            'email' => 'supervisor@test.com',
            'password' => bcrypt('password'),
        ]);

        // Assign the supervisor role to the user with halaqa scope
        $supervisor->assignRole($supervisorRole, $halaqa);

        // Test the relationship
        $loadedHalaqa = Halaqa::with('supervisor')->find($halaqa->id);

        $this->assertNotNull($loadedHalaqa->supervisor);
        $this->assertEquals($supervisor->id, $loadedHalaqa->supervisor->first()->id);
        $this->assertEquals('محمد أحمد', $loadedHalaqa->supervisor->first()->full_name);
    }

    /**
     * test halaqa students count
     */
    public function test_halaqa_students_count()
    {
        // Get a constant type for halaqa type
        $constantType = ConstantType::where('name', 'نوع الحلقة')->first();
        $this->assertNotNull($constantType, 'Constant type for halaqa should exist');

        // Create a constant for halaqa type
        $type = Constant::create([
            'name' => 'حلقة قرآن',
            'constant_type_id' => $constantType->id,
        ]);

        $halaqa = Halaqa::create([
            'name' => 'Test Halaqa',
            'location' => 'Test Location',
            'reference_type' => 'user',
            'reference_id' => 1,
            'type_id' => $type->id,
        ]);

        // Test initial count should be 0
        $this->assertEquals(0, $halaqa->studentsCount());

        // The studentsCount method should work without throwing errors
        $count = $halaqa->studentsCount();
        $this->assertIsInt($count);
    }

    /**
     * Test API response includes supervisor and students count
     */
    public function test_halaqa_api_response_includes_supervisor_and_count()
    {
        // Get a constant type for halaqa type
        $constantType = ConstantType::where('name', 'نوع الحلقة')->first();
        $this->assertNotNull($constantType, 'Constant type for halaqa should exist');

        // Create a constant for halaqa type
        $type = Constant::create([
            'name' => 'حلقة قرآن',
            'constant_type_id' => $constantType->id,
        ]);

        // Create required data
        $halaqa = Halaqa::create([
            'name' => 'API Test Halaqa',
            'location' => 'Test Location',
            'reference_type' => 'user',
            'reference_id' => 1,
            'type_id' => $type->id,
        ]);

        $supervisorRole = Role::firstOrCreate(['name' => 'محفظ']);
        $supervisor = User::create([
            'fName' => 'علي',
            'family' => 'محمد',
            'email' => 'ali@test.com',
            'password' => bcrypt('password'),
        ]);

        // Assign supervisor role
        $supervisor->assignRole($supervisorRole, $halaqa);

        // Create a user with permission to view halaqas
        $user = User::create([
            'fName' => 'Admin',
            'family' => 'User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        // Test API response
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/halaqas/{$halaqa->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'supervisor',
                    'students_count',
                ]
            ])
            ->assertJsonPath('data.supervisor.full_name', 'علي محمد')
            ->assertJsonPath('data.students_count', 0);
    }
}
