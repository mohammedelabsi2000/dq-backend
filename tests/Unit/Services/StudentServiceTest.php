<?php

namespace Tests\Unit\Services;

use App\Models\Branch;
use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use App\Models\Mosque;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{

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
                'SEX'                 => 'ذكر',
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*' => Http::response($this->fakeApiResponse, 200),
        ]);

        // ✅ identity ثابتة لا تتعارض مع بيانات الاختبارات
        $user = User::factory()->create(['identity' => '999999999']);
        $this->actingAs($user);

        $this->seedRequiredConstants($user->id);
        $this->service = app(StudentService::class);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * البنية الحقيقية للـ constants:
     *   constant_types (id, name)
     *       ↓
     *   constants (id, name, constant_type_id)
     *
     * لذا نُنشئ ConstantType أولاً ثم Constant مرتبط به
     */
    private function seedRequiredConstants(int $userId): void
    {
        $types = [
            'marital_status'    => ['أعزب'],
            'money_status'      => ['متوسط'],
            'name_prefix'       => ['الشيخ'],
            'guardian_type'     => ['أب'],
            'halaqa_types'      => ['تحفيظ'],
            'enrollment_status' => ['منتظم'],
        ];

        foreach ($types as $typeName => $constantNames) {
            $constantType = ConstantType::firstOrCreate(
                ['name' => $typeName],
                ['created_by' => $userId, 'updated_by' => $userId]
            );

            foreach ($constantNames as $name) {
                Constant::firstOrCreate(
                    ['name' => $name, 'constant_type_id' => $constantType->id],
                    ['is_active' => true, 'created_by' => $userId, 'updated_by' => $userId]
                );
            }
        }
    }

    /**
     * يجلب الـ Constant المطلوب عبر اسم الـ ConstantType واسم الـ Constant
     */
    private function getConstant(string $typeName, string $constantName): Constant
    {
        $typeId = ConstantType::where('name', $typeName)->value('id');
        return Constant::where('constant_type_id', $typeId)
            ->where('name', $constantName)
            ->firstOrFail();
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
     * ينشئ Halaqa بـ type_id صحيح وبدون Center
     * نُحدد reference_type = 'region' لتجنب إنشاء Center الذي يحتاج region_id إضافي
     */
    private function createHalaqa(): Halaqa
    {
        $typeId = $this->getConstant('halaqa_types', 'تحفيظ')->id;
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
     * gender = 'ذكر' لأن Gender enum معرّف: case Male = 'ذكر'
     */
    private function studentData(array $overrides = []): array
    {
        $mosque = $this->createMosque();
        // توليد رقم هوية عشوائي فريد لكل استدعاء
        $randomIdentity = (string) rand(100000000, 999999999);

        return array_merge([
            'fName'       => 'أحمد',
            'sName'       => 'محمد',
            'thName'      => 'علي',
            'family'      => 'الشمري',
            'identity'    => $randomIdentity, // قيمة عشوائية
            'guardian_id' => $randomIdentity, // قيمة عشوائية
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
        // نستخدم هوية محددة هنا فقط لنتأكد من عدم وجودها قبل الفحص
        $targetIdentity = '777666555';
        $data = $this->studentData([
            'identity' => '111222333', // هوية الطالب
            'guardian_id' => $targetIdentity // هوية ولي الأمر المطلوب فحصها
        ]);

        $this->assertDatabaseMissing('users', ['identity' => $targetIdentity]);

        $this->service->create($data);

        $this->assertDatabaseHas('users', ['identity' => $targetIdentity]);
    }

    /** @test */
    public function test_create_student_restores_trashed_guardian(): void
    {
        $targetIdentity = '555444333';
        $guardian = User::factory()->create(['identity' => $targetIdentity]);
        $guardian->delete();

        $this->assertSoftDeleted('users', ['identity' => $targetIdentity]);

        // نرسل بيانات الطالب مع تحديد ولي الأمر الذي حذفناه للتو
        $data = $this->studentData(['guardian_id' => $targetIdentity]);
        $this->service->create($data);

        $this->assertNotSoftDeleted('users', ['identity' => $targetIdentity]);
    }

    /** @test */
    public function test_create_student_reuses_existing_guardian(): void
    {
        $targetIdentity = '444555666';
        User::factory()->create(['identity' => $targetIdentity]);

        $data = $this->studentData(['guardian_id' => $targetIdentity]);
        $this->service->create($data);

        // التأكد من عدم تكرار ولي الأمر في قاعدة البيانات
        $this->assertSame(1, User::where('identity', $targetIdentity)->count());
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

        // نتحقق أن الصف القديم أُغلق (to_date) وليس محذوفاً، للحفاظ على السجل التاريخي
        $oldRecord = HalaqaStudent::withTrashed()
            ->where('student_id', $student->id)
            ->where('halaqa_id', $oldHalaqa->id)
            ->first();

        $this->assertNotNull($oldRecord);
        $this->assertFalse($oldRecord->trashed());
        $this->assertNotNull($oldRecord->to_date);

        $this->assertDatabaseHas('halaqa_students', [
            'student_id' => $student->id,
            'halaqa_id'  => $newHalaqa->id,
            'to_date'    => null,
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

        // نتحقق أن الصف أُغلق (to_date) وليس محذوفاً، للحفاظ على السجل التاريخي
        $record = HalaqaStudent::withTrashed()
            ->where('student_id', $student->id)
            ->where('halaqa_id', $halaqa->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertFalse($record->trashed());
        $this->assertNotNull($record->to_date);
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
        $constant = $this->getConstant('enrollment_status', 'منتظم');

        $this->service->assignStudentToHalaqa($student, $halaqa->id);

        $this->assertDatabaseHas('halaqa_students', [
            'student_id'           => $student->id,
            'enrollment_status_id' => $constant->id,
        ]);
    }
}
