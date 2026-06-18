<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Mosque;
use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Student;
use App\Enums\HalaqaReferenceType;
use Illuminate\Support\Facades\DB;

class HalaqaControllerTest extends TestCase
{
    protected User $adminUser;
    protected User $regularUser;
    protected Branch $branch;
    protected Region $region;
    protected Mosque $mosque;
    protected Center $center;
    protected int $typeId;
    protected int $statusTypeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    // ============================================================
    // 📋 index
    // ============================================================

    public function test_index_returns_halaqas()
    {
        Halaqa::factory()->count(3)->create([
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/halaqas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'total',
                'skip',
                'limit',
                'data' => [
                    '*' => ['id', 'name']
                ],
            ]);
    }

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/halaqas');

        $response->assertStatus(401);
    }

    public function test_index_requires_authorization()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/halaqas');

        $response->assertStatus(403);
    }

    public function test_index_search()
    {
        Halaqa::factory()->create([
            'name'           => 'حلقة الفجر',
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
            'is_approved'    => true,
        ]);
        Halaqa::factory()->create([
            'name'           => 'حلقة المغرب',
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
            'is_approved'    => true,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/halaqas?search=الفجر');

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('حلقة الفجر'));
        $this->assertFalse($names->contains('حلقة المغرب'));
    }

    public function test_index_filter_by_center()
    {
        $otherCenter = Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        Halaqa::factory()->create([
            'name'           => 'حلقة المركز الأول',
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
            'is_approved'    => true,
        ]);
        Halaqa::factory()->create([
            'name'           => 'حلقة المركز الثاني',
            'reference_id'   => $otherCenter->id,
            'reference_type' => HalaqaReferenceType::Center,
            'is_approved'    => true,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/halaqas?center_id={$this->center->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('حلقة المركز الأول'));
        $this->assertFalse($names->contains('حلقة المركز الثاني'));
    }

    public function test_index_filter_by_region()
    {
        $otherRegion = Region::factory()->create(['branch_id' => $this->branch->id]);

        Halaqa::factory()->create([
            'name'           => 'حلقة المنطقة الأولى',
            'reference_id'   => $this->region->id,
            'reference_type' => HalaqaReferenceType::Region,
            'is_approved'    => true,
        ]);
        Halaqa::factory()->create([
            'name'           => 'حلقة المنطقة الثانية',
            'reference_id'   => $otherRegion->id,
            'reference_type' => HalaqaReferenceType::Region,
            'is_approved'    => true,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/halaqas?region_id={$this->region->id}");

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('حلقة المنطقة الأولى'));
        $this->assertFalse($names->contains('حلقة المنطقة الثانية'));
    }

    public function test_index_filter_by_reference_type()
    {
        Halaqa::factory()->create([
            'name'           => 'حلقة مركز',
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
            'is_approved'    => true,
        ]);
        Halaqa::factory()->create([
            'name'           => 'حلقة منطقة',
            'reference_id'   => $this->region->id,
            'reference_type' => HalaqaReferenceType::Region,
            'is_approved'    => true,
        ]);

        // limit=* is important here because it means get all records
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/halaqas?limit=*&reference_type=' . HalaqaReferenceType::Center->value);

        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('حلقة مركز'));
        $this->assertFalse($names->contains('حلقة منطقة'));
    }

    public function test_index_includes_supervisors_in_response()
    {
        Halaqa::factory()->create([
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
            'is_approved'    => true,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/halaqas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'supervisors']
                ]
            ]);
    }

    // ============================================================
    // ➕ store
    // ============================================================

    public function test_store_creates_halaqa_under_center()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/halaqas', [
                'name'           => 'حلقة جديدة',
                'reference_id'   => $this->center->id,
                'reference_type' => HalaqaReferenceType::Center->value,
                'type_id'        => $this->typeId,
                'status_type_id' => $this->statusTypeId,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'حلقة جديدة');

        $this->assertDatabaseHas('halaqas', [
            'name'           => 'حلقة جديدة',
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center->value,
        ]);
    }

    public function test_store_creates_halaqa_under_region()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/halaqas', [
                'name'           => 'حلقة منطقة',
                'reference_id'   => $this->region->id,
                'reference_type' => HalaqaReferenceType::Region->value,
                'type_id'        => $this->typeId,
                'status_type_id' => $this->statusTypeId,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('halaqas', [
            'name'           => 'حلقة منطقة',
            'reference_id'   => $this->region->id,
            'reference_type' => HalaqaReferenceType::Region->value,
        ]);
    }

    public function test_store_validation()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/halaqas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'reference_id', 'reference_type']);
    }

    // ============================================================
    // 👁️ show
    // ============================================================

    public function test_show_returns_halaqa()
    {
        $halaqa = Halaqa::factory()->create([
            'name'           => 'حلقة الفجر',
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/halaqas/{$halaqa->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'حلقة الفجر')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'supervisors']
            ]);
    }

    public function test_show_requires_authorization()
    {
        $halaqa = Halaqa::factory()->create([
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson("/api/halaqas/{$halaqa->id}");

        $response->assertStatus(403);
    }

    public function test_show_includes_students_when_requested()
    {
        $halaqa = Halaqa::factory()->create([
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/halaqas/{$halaqa->id}?with_students=true");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'students']
            ]);
    }

    // ============================================================
    // ✏️ update
    // ============================================================

    public function test_update_modifies_halaqa()
    {
        $halaqa = Halaqa::factory()->create([
            'name'           => 'الاسم القديم',
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/halaqas/{$halaqa->id}", [
                'name'           => 'الاسم الجديد',
                'center_id'      => $this->center->id, // استخدم center_id ليتم معالجته في prepareForValidation
                'type_id'        => $halaqa->type_id,
                'status_type_id' => $this->statusTypeId,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'الاسم الجديد');

        $this->assertDatabaseHas('halaqas', [
            'id'   => $halaqa->id,
            'name' => 'الاسم الجديد',
        ]);
    }

    public function test_update_requires_authorization()
    {
        $halaqa = Halaqa::factory()->create([
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson("/api/halaqas/{$halaqa->id}", [
                'name'           => 'اسم جديد',
                'reference_id'   => $this->center->id,
                'reference_type' => HalaqaReferenceType::Center->value,
            ]);

        $response->assertStatus(403);
    }

    // ============================================================
    // 🗑️ destroy
    // ============================================================

    public function test_destroy_deletes_halaqa()
    {
        $halaqa = Halaqa::factory()->create([
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/halaqas/{$halaqa->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('halaqas', [
            'id'         => $halaqa->id,
            'deleted_at' => null,
        ]);
    }

    public function test_destroy_fails_with_students()
    {
        // 1. إنشاء الحلقة
        $halaqa = Halaqa::factory()->create([
            'reference_id'   => $this->center->id,
            'reference_type' => 'center',
        ]);

        // 2. إنشاء طالب (بدون halaqa_id لأنه غير موجود في جدوله)
        $student = Student::factory()->create();

        // 3. ربط الطالب بالحلقة في الجدول الوسيط
        // افترضت هنا أن اسم الموديل الوسيط هو HalaqaStudent
        DB::table('halaqa_students')->insert([
            'halaqa_id'            => $halaqa->id,
            'student_id'           => $student->id,
            'from_date'            => now(),
            'enrollment_status_id' => 1, // تأكد من وجود ID صالح من الـ constants
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // 4. محاولة الحذف
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/halaqas/{$halaqa->id}");

        // يجب أن يعود بـ 400 لأن الحلقة مرتبطة بطلاب
        $response->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('halaqas', ['id' => $halaqa->id]);
    }

    public function test_destroy_requires_authorization()
    {
        $halaqa = Halaqa::factory()->create([
            'reference_id'   => $this->center->id,
            'reference_type' => HalaqaReferenceType::Center,
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson("/api/halaqas/{$halaqa->id}");

        $response->assertStatus(403);
    }

    // ============================================================
    // 🔧 Helpers
    // ============================================================

    protected function seedBaseData(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\ConstantTypeSeeder::class);
        $type = \App\Models\ConstantType::where('name', 'halaqa_types')->first();
        $this->typeId = $type->constants()->first()->id;
        $statusType = \App\Models\ConstantType::where('name', 'status_type')->first();
        $this->statusTypeId = $statusType->constants()->first()->id;

        $this->branch = Branch::factory()->create();
        $this->region = Region::factory()->create(['branch_id' => $this->branch->id]);
        $this->mosque = Mosque::factory()->create(['region_id' => $this->region->id]);
        $this->center = Center::factory()->create([
            'region_id' => $this->region->id,
            'mosque_id' => $this->mosque->id,
        ]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('مدير الدائرة');

        $this->regularUser = User::factory()->create();
    }
}
