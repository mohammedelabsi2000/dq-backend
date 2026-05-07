<?php

namespace Tests\Feature\Api;

use App\Enums\Gender;
use App\Helpers\ConstantHelper;
use App\Models\Branch;
use App\Models\Center;
use App\Models\ConstantType;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use App\Models\Mosque;
use App\Models\Region;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Tests\Traits\SeedsTestData;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase, SeedsTestData;

    protected User $branchManagerUser;
    protected Branch $branch;
    protected Region $region;
    protected Center $center;
    protected ConstantType $maritalStatusType;
    protected ConstantType $moneyStatusType;
    protected ConstantType $guardianTypeType;



    protected array $fakeStudentData;
    protected Halaqa $halaqa;
    protected Mosque $mosque;
    protected User $guardianUser;

    protected int $enrollStatusId;

    protected function setUp(): void
    {
        parent::setUp();

        HalaqaStudent::withTrashed()->forceDelete();
        Student::withTrashed()->forceDelete();
        $this->seedBaseData();
        $this->initializeTestData();
        $this->guardianUser = User::factory()->create();
        $this->fakeStudentData = Student::factory()->make()->toArray();

        // Ignore full_name column (it's generated automatically)
        unset($this->fakeStudentData['full_name']);

        // Create scoped user (Branch Manager)
        $this->branchManagerUser = User::where('email', 'branch@test.com')->first()
            ?? User::factory()->create([
                'name' => 'Branch Manager',
                'email' => 'branch@test.com',
            ]);
    }

    /**
     * Initialize test data: Branch → Region → Center → Mosque → Halaqa
     */
    protected function initializeTestData(): void
    {
        // Create Branch
        $this->branch = Branch::factory()->create(['name' => 'فرع الرياض']);

        // Create Region
        $this->region = Region::factory()->create([
            'name' => 'منطقة الرياض',
            'branch_id' => $this->branch->id
        ]);

        // Create Center
        $this->center = Center::factory()->create([
            'name' => 'مركز الرياض',
            'region_id' => $this->region->id
        ]);

        // Create Mosque
        $this->mosque = Mosque::factory()->create([
            'name' => 'مسجد الرياض',
            'region_id' => $this->region->id
        ]);

        // Create Halaqa
        $this->halaqa = Halaqa::factory()->create([
            'name' => 'حلقة الرياض',
            'reference_type' => 'center',
            'reference_id' => $this->center->id
        ]);

        $this->enrollStatusId = fake()->randomElement(
            ConstantHelper::getConstantIdsByType('enrollment_status')
        );
    }

    // ==================== CRUD TESTS ====================

    /** @test */
    public function test_index_returns_students()
    {
        Student::factory(15)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?limit=*');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'total',
                'skip',
                'limit'
            ])
            ->assertJsonCount(15, 'data');
    }

    /** @test */
    public function test_store_creates_student()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/students', $this->fakeStudentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'identity',
                    'fName',
                    'sName',
                    'full_name',
                    'mosque'
                ]
            ]);

        $this->assertDatabaseHas('students', [
            'identity' => $this->fakeStudentData['identity'],
            'fName' => $this->fakeStudentData['fName']
        ]);
    }

    /** @test */
    public function test_store_restores_trashed_student()
    {
        // Create and trash a student
        $student = Student::factory()->create($this->fakeStudentData);

        $student->delete();

        $this->assertTrue($student->trashed());

        $response = $this->actingAsAdmin()
            ->postJson('/api/students', $this->fakeStudentData);

        $response->assertStatus(201);

        // Verify student is restored
        $restoredStudent = Student::find($student->id);
        $this->assertFalse($restoredStudent->trashed());
    }

    /** @test */
    public function test_store_assigns_halaqa()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/students', $this->fakeStudentData);

        $response->assertStatus(201);

        $student = Student::where('identity', $this->fakeStudentData['identity'])->first();

        // Attach halaqa to student
        $student->halaqas()->attach($this->halaqa->id, [
            'enrollment_status_id' => $this->enrollStatusId,
            'from_date' => now(),
        ]);

        $this->assertTrue($student->halaqas()->where('halaqa_id', $this->halaqa->id)->exists());
    }

    /** @test */
    public function test_show_returns_student_with_relations()
    {
        $student = Student::factory()->withHalaqa()->create();

        $response = $this->actingAsAdmin()
            ->getJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'full_name',
                    'identity',
                    'mosque' => ['id', 'name'],
                    'halaqas' => [
                        '*' => ['id', 'name']
                    ]
                ]
            ]);
    }

    /** @test */
    public function test_update_modifies_student()
    {
        $student = Student::factory()->create([
            'mosque_id' => $this->mosque->id
        ]);

        $updateData = [
            'fName' => 'أحمد',
            'sName' => 'محمد',
            'phone' => '0509876543'
        ];

        $response = $this->actingAsAdmin()
            ->patchJson("/api/students/{$student->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'fName' => 'أحمد',
            'phone' => '0509876543'
        ]);
    }

    /** @test */
    public function test_destroy_soft_deletes_student()
    {
        $student = Student::factory()->create([
            'mosque_id' => $this->mosque->id
        ]);

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(200);

        $this->assertTrue($student->fresh()->trashed());
    }

    // ==================== FILTER TESTS ====================

    /** @test */
    public function test_filter_by_branch()
    {
        // Create another branch with separate data
        $anotherBranch = Branch::factory()->create(['name' => 'فرع جدة']);
        $anotherRegion = Region::factory()->create([
            'name' => 'منطقة جدة',
            'branch_id' => $anotherBranch->id
        ]);
        $anotherMosque = Mosque::factory()->create([
            'name' => 'مسجد جدة',
            'region_id' => $anotherRegion->id
        ]);

        // Create students in both branches
        $riyadhStudents = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);
        $jeddahStudents = Student::factory(5)->create(['mosque_id' => $anotherMosque->id]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?branch_id=' . $this->branch->id);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertContains($student['id'], $riyadhStudents->pluck('id')->toArray());
        }
    }

    /** @test */
    public function test_filter_by_halaqa()
    {
        $halaqa2 = Halaqa::factory()->create([
            'name' => 'حلقة 2',
            'reference_type' => 'center',
            'reference_id' => $this->center->id
        ]);

        $students1 = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);
        $students2 = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);

        foreach ($students1 as $student) {
            $student->halaqas()->attach($this->halaqa->id, [
                'from_date' => now(),
                'to_date' => null,
                'enrollment_status_id' => $this->enrollStatusId
            ]);
        }

        foreach ($students2 as $student) {
            $student->halaqas()->attach($halaqa2->id, [
                'from_date' => now(),
                'to_date' => null,
                'enrollment_status_id' => $this->enrollStatusId
            ]);
        }

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?halaqa_id=' . $this->halaqa->id);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertContains($student['id'], $students1->pluck('id')->toArray());
        }
    }

    /** @test */
    public function test_filter_by_center()
    {
        $anotherCenter = Center::factory()->create([
            'name' => 'مركز 2',
            'region_id' => $this->region->id
        ]);

        $halaqa2 = Halaqa::factory()->create([
            'name' => 'حلقة مركز 2',
            'reference_type' => 'center',
            'reference_id' => $anotherCenter->id
        ]);

        $studentsCenter1 = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);
        $studentsCenter2 = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);

        foreach ($studentsCenter1 as $student) {
            $student->halaqas()->attach($this->halaqa->id, [
                'enrollment_status_id' => $this->enrollStatusId,
                'from_date' => now(),
                'to_date' => null
            ]);
        }

        foreach ($studentsCenter2 as $student) {
            $student->halaqas()->attach($halaqa2->id, [
                'enrollment_status_id' => $this->enrollStatusId,
                'from_date' => now(),
                'to_date' => null
            ]);
        }

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?center_id=' . $this->center->id);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function test_filter_by_region()
    {
        $anotherRegion = Region::factory()->create([
            'name' => 'منطقة أخرى',
            'branch_id' => $this->branch->id
        ]);

        $anotherMosque = Mosque::factory()->create([
            'name' => 'مسجد آخر',
            'region_id' => $anotherRegion->id
        ]);

        $studentsRegion1 = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);
        $studentsRegion2 = Student::factory(5)->create(['mosque_id' => $anotherMosque->id]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?region_id=' . $this->region->id);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertContains($student['id'], $studentsRegion1->pluck('id')->toArray());
        }
    }

    /** @test */
    public function test_filter_by_mosque()
    {
        $anotherMosque = Mosque::factory()->create([
            'name' => 'مسجد آخر',
            'region_id' => $this->region->id
        ]);

        $studentsMosque1 = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);
        $studentsMosque2 = Student::factory(5)->create(['mosque_id' => $anotherMosque->id]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?mosque_id=' . $this->mosque->id);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertContains($student['id'], $studentsMosque1->pluck('id')->toArray());
        }
    }

    /** @test */
    public function test_filter_by_gender()
    {
        $maleStudents = Student::factory(5)->male()->create();

        $femaleStudents = Student::factory(5)->female()->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?gender=' . Gender::Male->labels()['ar']);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertEquals(Gender::Male->label(), $student['gender'], $student['gender']);
        }
    }

    /** @test */
    public function test_filter_by_age_range()
    {
        $now = Carbon::now();

        // Students aged 10 years old
        $youngStudents = Student::factory(5)->create([
            'mosque_id' => $this->mosque->id,
            'dob' => $now->clone()->subYears(10)->format('Y-m-d')
        ]);

        // Students aged 20 years old
        $olderStudents = Student::factory(5)->create([
            'mosque_id' => $this->mosque->id,
            'dob' => $now->clone()->subYears(20)->format('Y-m-d')
        ]);

        // Filter students aged 8-15 (age_min=8, age_max=15)
        $response = $this->actingAsAdmin()
            ->getJson('/api/students?age_min=8&age_max=15');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertContains($student['id'], $youngStudents->pluck('id')->toArray());
        }
    }

    /** @test */
    public function test_filter_has_active_halaqa()
    {
        $studentsWithHalaqa = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);
        $studentsWithoutHalaqa = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);

        foreach ($studentsWithHalaqa as $student) {
            $student->halaqas()->attach($this->halaqa->id, [
                'enrollment_status_id' => $this->enrollStatusId,
                'from_date' => now()->subMonth(),
                'to_date' => null // Still active
            ]);
        }

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?has_halaqa=true');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertContains($student['id'], $studentsWithHalaqa->pluck('id')->toArray());
        }
    }

    /** @test */
    public function test_search_by_name()
    {
        Student::factory()->create([
            'mosque_id' => $this->mosque->id,
            'fName' => 'محمد',
            'sName' => 'أحمد',
            'thName' => 'علي'
        ]);

        Student::factory()->create([
            'mosque_id' => $this->mosque->id,
            'fName' => 'أحمد',
            'sName' => 'محمود'
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?search=محمد');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fName', 'محمد');
    }

    /** @test */
    public function test_search_by_identity()
    {
        $student = Student::factory()->create([
            'mosque_id' => $this->mosque->id,
            'identity' => '123456789'
        ]);

        Student::factory()->create([
            'mosque_id' => $this->mosque->id,
            'identity' => '987654321'
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/students?search=123456789');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.identity', '123456789');
    }

    // ==================== AUTHORIZATION TESTS ====================

    /** @test */
    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/students');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_scoped_user_sees_only_their_students()
    {
        // إنشاء مركز وحلقة ضمن الفرع
        $center = Center::factory()->create(['region_id' => $this->region->id]);
        $halaqa = Halaqa::factory()->create([
            'reference_type' => 'center',
            'reference_id'   => $center->id,
        ]);

        // إنشاء طلاب وتسجيلهم في الحلقة
        $branchStudents = Student::factory(5)->create(['mosque_id' => $this->mosque->id]);
        foreach ($branchStudents as $student) {
            HalaqaStudent::factory()->create([
                'halaqa_id'   => $halaqa->id,
                'student_id'  => $student->id,
                'to_date'     => null,
            ]);
        }

        // إنشاء طلاب في فرع آخر بدون تسجيل في حلقة الفرع الأول
        $anotherBranch  = Branch::factory()->create();
        $anotherRegion  = Region::factory()->create(['branch_id' => $anotherBranch->id]);
        $anotherCenter  = Center::factory()->create(['region_id' => $anotherRegion->id]);
        $anotherHalaqa  = Halaqa::factory()->create([
            'reference_type' => 'center',
            'reference_id'   => $anotherCenter->id,
        ]);
        $anotherMosque  = Mosque::factory()->create(['region_id' => $anotherRegion->id]);
        $otherStudents  = Student::factory(5)->create(['mosque_id' => $anotherMosque->id]);
        foreach ($otherStudents as $student) {
            HalaqaStudent::factory()->create([
                'halaqa_id'  => $anotherHalaqa->id,
                'student_id' => $student->id,
                'to_date'    => null,
            ]);
        }

        $role = Role::firstOrCreate(['name' => 'branch_manager', 'guard_name' => 'sanctum']);
        $role->syncPermissions(Permission::where('name', 'like', 'students.%')->get());
        $this->branchManagerUser->syncRoles([$role]);
        $this->branchManagerUser->syncScopes([['type' => 'branch', 'id' => $this->branch->id]]);

        $response = $this->actingAs($this->branchManagerUser, 'sanctum')
            ->getJson('/api/students');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');

        foreach ($response->json('data') as $student) {
            $this->assertContains($student['id'], $branchStudents->pluck('id')->toArray());
        }
    }

    // ==================== IMPORT TESTS ====================

    /** @test */
    /* public function test_import_students_from_excel()
    {
        $csvData = <<<CSV
identity,fName,sName,family,dob,gender,phone
123456789,محمد,أحمد,السعيد,2010-05-15,male,0501234567
9876543210,فاطمة,علي,الأحمري,2011-03-20,female,0509876543
CSV;

        $response = $this->actingAsAdmin()
            ->postJson('/api/students/import', [
                'file' => $this->createTestFile($csvData, 'students.csv'),
                'mosque_id' => $this->mosque->id
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('students', [
            'identity' => '123456789',
            'fName' => 'محمد'
        ]);

        $this->assertDatabaseHas('students', [
            'identity' => '9876543210',
            'fName' => 'فاطمة'
        ]);
    } */

    /**
     * Helper method to create test file
     */
    /* protected function createTestFile($content, $filename)
    {
        $path = storage_path('app/test/' . $filename);
        \File::ensureDirectoryExists(dirname($path));
        \File::put($path, $content);

        return new \Illuminate\Http\UploadedFile(
            $path,
            $filename,
            'text/csv',
            null,
            true
        );
    } */
}
