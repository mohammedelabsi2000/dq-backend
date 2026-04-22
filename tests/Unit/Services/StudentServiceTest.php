<?php

namespace Tests\Unit\Services;

use App\Models\Branch;
use App\Models\Constant;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use App\Models\Mosque;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    private StudentService $service;

    // بيانات وهمية تُحاكي رد الـ API الخارجي
    private array $fakeApiResponse = [
        'DATA' => [
            [
                'CI_ID_NUM'           => '123456789',
                'CI_FIRST_ARB'        => 'محمد',
                'CI_FATHER_ARB'       => 'أحمد',
                'CI_GRAND_FATHER_ARB' => 'علي',
                'CI_FAMILY_ARB'       => 'الغامدي',
                'CI_BIRTH_DT'         => '01/01/1980',
                'SEX'                 => 'M',
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // نوقف كل HTTP calls ونرجع بيانات وهمية بدل استدعاء الـ API
        // السبب: IdQueryServices يُنشأ داخل StudentService مباشرة (new IdQueryServices)
        // لذا $this->mock() لا يعمل — Http::fake() هو الحل الصحيح
        Http::fake([
            '*' => Http::response($this->fakeApiResponse, 200),
        ]);

        $this->seedRequiredConstants();
        $this->service = app(StudentService::class);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * ينشئ الـ Constants الضرورية لعمل الـ factories والـ service
     */
    private function seedRequiredConstants(): void
    {
        $required = [
            'marital_status'    => ['أعزب'],
            'money_status'      => ['متوسط'],
            'name_prefix'       => ['الشيخ'],
            'guardian_type'     => ['أب'],
            'halaqa_types'      => ['تحفيظ'],
            'enrollment_status' => ['منتظم'],
        ];

        foreach ($required as $type => $names) {
            foreach ($names as $name) {
                Constant::firstOrCreate(['type' => $type, 'name' => $name]);
            }
        }
    }

    /**
     * ينشئ Mosque مع hierarchy كاملة: Branch → Region → Mosque
     */
    private function createMosque(): Mosque
    {
        $branch = Branch::factory()->create();
        $region = Region::factory()->create(['branch_id' => $branch->id]);
        return Mosque::factory()->create(['region_id' => $region->id]);
    }

    /**
     * ينشئ Halaqa مع type_id صحيح وبدون Center
     * نُحدد reference_type = 'region' لتجنب إنشاء Center الذي يحتاج region_id إضافي
     */
    private function createHalaqa(): Halaqa
    {
        $typeId = Constant::where('type', 'halaqa_types')->value('id');
        $branch = Branch::factory()->create();
        $region = Region::factory()->create(['branch_id' => $branch->id]);

        return Halaqa::factory()->create([
            'type_id'        => $typeId,
            'reference_type' => 'region',
            'reference_id'   => $region->id,
        ]);
    }

    /**
     * بيانات الطالب الأساسية
     * gender = 'ذكر' — القيمة الصحيحة حسب Gender enum (Case Male = 'ذكر')
     */
    private function studentData(array $overrides = []): array
    {
        $mosque = $this->createMosque();

        return array_merge([
            'fName'       => 'أحمد',
            'sName'       => 'محمد',
            'thName'      => 'علي',
            'family'      => 'الشمري',
            'identity'    => '123456789',
            'guardian_id' => '123456789',
            'mosque_id'   => $mosque->id,
            'gender'      => 'ذكر',
            'dob'         => '2005-01-01',
            'phone'       => '0501234567',
        ], $overrides);
    }

    // ─────────────────────────────────────────────
    // Tests: create()
    // ─────────────────────────────────────────────

    /** @test */
    public function test_create_student(): void
    {
        $student = $this->service->create($this->studentData());

        $this->assertInstanceOf(Student::class, $student);
        $this->assertDatabaseHas('students', [
            'fName'  => 'أحمد',
            'family' => 'الشمري',
        ]);
    }

    /** @test */
    public function test_create_student_with_halaqa(): void
    {
        $halaqa = $this->createHalaqa();
        $data   = $this->studentData(['halaqa_id' => $halaqa->id]);

        $student = $this->service->create($data);

        $this->assertDatabaseHas('halaqa_students', [
            'student_id' => $student->id,
            'halaqa_id'  => $halaqa->id,
        ]);
    }

    /** @test */
    public function test_create_student_without_halaqa_does_not_create_pivot(): void
    {
        $student = $this->service->create($this->studentData());

        $this->assertDatabaseMissing('halaqa_students', [
            'student_id' => $student->id,
        ]);
    }

    /** @test */
    public function test_create_student_creates_guardian_when_not_exists(): void
    {
        $this->assertDatabaseMissing('users', ['identity' => '123456789']);

        $this->service->create($this->studentData());

        $this->assertDatabaseHas('users', ['identity' => '123456789']);
    }

    /** @test */
    public function test_create_student_restores_trashed_guardian(): void
    {
        $guardian = User::factory()->create(['identity' => '123456789']);
        $guardian->delete();

        $this->assertSoftDeleted('users', ['identity' => '123456789']);

        $this->service->create($this->studentData());

        $this->assertNotSoftDeleted('users', ['identity' => '123456789']);
    }

    /** @test */
    public function test_create_student_reuses_existing_guardian(): void
    {
        User::factory()->create(['identity' => '123456789']);

        $this->service->create($this->studentData());

        // Guardian واحد فقط — لم يُكرَّر
        $this->assertSame(1, User::where('identity', '123456789')->count());
    }

    // ─────────────────────────────────────────────
    // Tests: update()
    // ─────────────────────────────────────────────

    /** @test */
    public function test_update_student(): void
    {
        $student = Student::factory()->inMosque($this->createMosque()->id)->create();

        $this->service->update($student, ['fName' => 'اسم محدّث']);

        $this->assertDatabaseHas('students', [
            'id'    => $student->id,
            'fName' => 'اسم محدّث',
        ]);
    }

    /** @test */
    public function test_update_student_halaqa_assignment(): void
    {
        $student   = Student::factory()->inMosque($this->createMosque()->id)->create();
        $oldHalaqa = $this->createHalaqa();
        $newHalaqa = $this->createHalaqa();

        HalaqaStudent::factory()->create([
            'student_id' => $student->id,
            'halaqa_id'  => $oldHalaqa->id,
        ]);

        $this->service->update($student, ['halaqa_id' => $newHalaqa->id]);

        $this->assertDatabaseMissing('halaqa_students', [
            'student_id' => $student->id,
            'halaqa_id'  => $oldHalaqa->id,
        ]);
        $this->assertDatabaseHas('halaqa_students', [
            'student_id' => $student->id,
            'halaqa_id'  => $newHalaqa->id,
        ]);
    }

    /** @test */
    public function test_update_student_with_null_halaqa_removes_assignment(): void
    {
        $student = Student::factory()->inMosque($this->createMosque()->id)->create();
        $halaqa  = $this->createHalaqa();

        HalaqaStudent::factory()->create([
            'student_id' => $student->id,
            'halaqa_id'  => $halaqa->id,
        ]);

        $this->service->update($student, ['halaqa_id' => null]);

        $this->assertDatabaseMissing('halaqa_students', [
            'student_id' => $student->id,
        ]);
    }

    // ─────────────────────────────────────────────
    // Tests: assignStudentToHalaqa()
    // ─────────────────────────────────────────────

    /** @test */
    public function test_assign_student_to_halaqa_creates_pivot_with_correct_data(): void
    {
        $student = Student::factory()->inMosque($this->createMosque()->id)->create();
        $halaqa  = $this->createHalaqa();

        $this->service->assignStudentToHalaqa($student, $halaqa->id);

        $this->assertDatabaseHas('halaqa_students', [
            'student_id' => $student->id,
            'halaqa_id'  => $halaqa->id,
            'from_date'  => now()->toDateString(),
        ]);
    }

    /** @test */
    public function test_assign_student_to_halaqa_sets_enrollment_status(): void
    {
        $student  = Student::factory()->inMosque($this->createMosque()->id)->create();
        $halaqa   = $this->createHalaqa();
        $constant = Constant::where('type', 'enrollment_status')
            ->where('name', 'منتظم')
            ->first();

        $this->service->assignStudentToHalaqa($student, $halaqa->id);

        $this->assertDatabaseHas('halaqa_students', [
            'student_id'           => $student->id,
            'enrollment_status_id' => $constant->id,
        ]);
    }
}
