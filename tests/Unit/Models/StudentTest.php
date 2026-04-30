<?php

namespace Tests\Unit\Models;

use App\Enums\Gender;
use App\Helpers\ConstantHelper;
use App\Models\Student;
use App\Models\User;
use App\Models\Mosque;
use App\Models\Halaqa;
use App\Models\Constant;
use Tests\TestCase;

class StudentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /**
     * Test basic model attributes and mass assignment
     */
    public function test_fillable_attributes()
    {
        $fillable = [
            'identity',
            'fName',
            'sName',
            'thName',
            'family',
            'dob',
            'mosque_id',
            'location',
            'gender',
            'marital_status_id',
            'money_status_id',
            'prefix_name_id',
            'guardian_id',
            'guardian_type_id',
            'phone',
            'whatsapp',
            'created_by',
            'updated_by',
            'memorized_juz',
            'completed_juz',
            'surah_id',
            'end_aya'
        ];

        // Assert that the model's fillable attributes match the expected array with canonical ordering
        $this->assertEqualsCanonicalizing(
            $fillable,
            (new Student)->getFillable()
        );
    }

    public function test_casts_attributes()
    {
        $casts = [
            'dob' => 'date',
            'gender' => 'App\Enums\Gender'
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, (new Student)->getCasts()[$attribute] ?? null);
        }
    }

    public function test_dob_is_cast_to_date()
    {
        $student = Student::factory()->create([
            'dob' => '2000-01-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $student->dob);
    }

    /**
     * Test model relationships
     */
    public function test_mosque_relationship()
    {
        $mosque = Mosque::factory()->create();
        $student = Student::factory()->create([
            'mosque_id' => $mosque->id,
        ]);

        $this->assertInstanceOf(Mosque::class, $student->mosque);
    }

    public function test_marital_status_relationship()
    {
        $student = Student::factory()->create();
        $maritalStatus = Constant::factory()->create(['constant_type_id' => 1]);

        $student->marital_status_id = $maritalStatus->id;
        $student->save();

        $this->assertInstanceOf(Constant::class, $student->maritalStatus);
        $this->assertEquals($maritalStatus->id, $student->marital_status_id);
    }

    public function test_money_status_relationship()
    {
        $student = Student::factory()->create();
        $moneyStatus = Constant::factory()->create(['constant_type_id' => 4]);

        $student->money_status_id = $moneyStatus->id;
        $student->save();

        $this->assertInstanceOf(Constant::class, $student->moneyStatus);
        $this->assertEquals($moneyStatus->id, $student->money_status_id);
    }

    public function test_prefix_name_relationship()
    {
        $student = Student::factory()->create();
        $prefixName = Constant::factory()->create(['constant_type_id' => 2]);

        $student->prefix_name_id = $prefixName->id;
        $student->save();

        $this->assertInstanceOf(Constant::class, $student->prefixName);
        $this->assertEquals($prefixName->id, $student->prefix_name_id);
    }

    public function test_guardian_relationship()
    {
        $user = User::where('identity', '123456789')->first()
            ?? User::factory()->create([
                'identity' => '123456789',
            ]);

        $student = Student::where('guardian_id', '123456789')->first()
            ?? Student::factory()->create([
                'guardian_id' => '123456789',
            ]);

        $this->assertInstanceOf(User::class, $student->guardian);
    }

    public function test_guardian_type_relationship()
    {
        $student = Student::factory()->create();
        $guardianType = Constant::factory()->create(['constant_type_id' => 6]);

        $student->guardian_type_id = $guardianType->id;
        $student->save();

        $this->assertInstanceOf(Constant::class, $student->guardianType);
        $this->assertEquals($guardianType->id, $student->guardian_type_id);
    }

    public function test_halaqas_relationship()
    {
        $student = Student::factory()->create();
        $halaqa = Halaqa::factory()->create();

        $statusId = fake()->randomElement(
            ConstantHelper::getConstantIdsByType('enrollment_status')
        );

        $student->halaqas()->attach($halaqa->id, [
            'enrollment_status_id' => $statusId,
            'from_date' => now(),
        ]);

        $this->assertTrue($student->halaqas->contains($halaqa));
        $this->assertArrayHasKey('from_date', $student->halaqas->first()->pivot->getAttributes());
    }

    /**
     * Test model accessors and computed attributes
     */
    public function test_full_name_attribute()
    {
        $student = Student::factory()->make([
            'fName' => 'محمد',
            'sName' => 'أحمد',
            'thName' => 'علي',
            'family' => 'المدهون'
        ]);

        $this->assertEquals('محمد أحمد علي المدهون', $student->full_name);
    }

    public function test_full_name_with_missing_parts()
    {
        $student = Student::factory()->make([
            'fName' => 'محمد',
            'sName' => null,
            'thName' => null,
            'family' => 'خالد',
        ]);

        $this->assertEquals('محمد خالد', $student->full_name);
    }

    public function test_images_relationship()
    {
        $student = Student::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $student->images());
    }

    public function test_main_image_relationship()
    {
        $student = Student::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $student->mainImage());
    }

    /**
     * Test model scopes and query methods
     */
    public function test_visible_to_scope()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create();

        $visibleStudents = Student::visibleTo($user)->get();
        $this->assertTrue($visibleStudents->contains($student));
    }

    public function test_by_guardian_scope()
    {
        $guardian = User::factory()->create();
        $student = Student::factory()->create(['guardian_id' => $guardian->identity]);

        $students = Student::byGuardian($guardian->identity)->get();
        $this->assertTrue($students->contains($student));
    }

    public function test_by_mosque_scope()
    {
        $mosque = Mosque::factory()->create();
        $student = Student::factory()->create(['mosque_id' => $mosque->id]);

        $students = Student::byMosque($mosque->id)->get();
        $this->assertTrue($students->contains($student));
    }

    /**
     * Test model methods and business logic
     */
    public function test_get_age_method()
    {
        $student = Student::factory()->create(['dob' => '2000-01-01']);

        // Assuming current date is 2026-01-01 for testing
        $this->assertEquals(26, $student->age);
    }

    public function test_is_active_in_halaqa_method()
    {
        $student = Student::factory()->create();
        $halaqa = Halaqa::factory()->create();

        // Attach with no end date (active)
        $student->halaqas()->attach($halaqa->id, [
            'from_date' => now()->subMonth(),
            'enrollment_status_id' => ConstantHelper::getConstantIdsByType('enrollment_status')[0], // Assuming 'منظم' is first
        ]);

        $this->assertTrue($student->isActiveInHalaqa($halaqa->id));
    }

    /**
     * Test factory states and data generation
     */
    public function test_male_factory_state()
    {
        $student = Student::factory()->male()->create();

        $this->assertEquals(Gender::Male, $student->gender);
    }

    public function test_female_factory_state()
    {
        $student = Student::factory()->female()->create();

        $this->assertEquals(Gender::Female, $student->gender);
    }

    public function test_with_guardian_factory_state()
    {
        $guardian = User::factory()->create();
        $student = Student::factory()->withGuardian($guardian)->create();

        $this->assertEquals($guardian->identity, $student->guardian_id);
        $this->assertInstanceOf(User::class, $student->guardian);
    }

    public function test_in_mosque_factory_state()
    {
        $mosque = Mosque::factory()->create();
        $student = Student::factory()->inMosque($mosque->id)->create();

        $this->assertEquals($mosque->id, $student->mosque_id);
        $this->assertInstanceOf(Mosque::class, $student->mosque);
    }

    /**
     * Test model events and observers
     */
    public function test_soft_deletes()
    {
        $student = Student::factory()->create();
        $studentId = $student->id;

        $student->delete();

        $this->assertSoftDeleted($student);
        $this->assertNull(Student::find($studentId));
        $this->assertNotNull(Student::withTrashed()->find($studentId));
    }

    public function test_audit_trail()
    {
        $this->assertTrue(Student::$usesAudit);
    }

    /**
     * Test validation and business rules
     */
    public function test_unique_identity_validation()
    {
        $existingStudent = Student::factory()->create(['identity' => '123456789']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Student::factory()->create(['identity' => '123456789']);
    }
}
