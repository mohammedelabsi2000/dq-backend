<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\Mosque;
use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود بيانات أساسية قبل إنشاء الطلاب
        $this->ensureRequiredDataExists();

        // إنشاء 50 طالب عشوائي
        Student::factory(50)->create();

        // إنشاء طلاب إضافيين لمسجد معين
        $this->createStudentsForSpecificMosque();

        // إنشاء طلاب مع أولياء أمور محددين
        $this->createStudentsWithSpecificGuardians();

        // إنشاء طلاب ببيانات محددة
        $this->createSpecificStudents();
    }

    /**
     * التأكد من وجود البيانات المطلوبة
     */
    private function ensureRequiredDataExists(): void
    {
        /* // التأكد من وجود مساجد
        if (Mosque::count() === 0) {
            $this->command->info('جاري إنشاء مساجد افتراضية...');
            \Database\Seeders\MosqueSeeder::class;
        } */

        // التأكد من وجود المستخدمين (أولياء الأمور)
        if (User::count() === 0) {
            $this->command->info('جاري إنشاء مستخدمين افتراضيين...');
            User::factory(20)->create();
        }

        // التأكد من وجود الثوابت المطلوبة
        $this->ensureConstantsExist();
    }

    /**
     * التأكد من وجود الثوابت المطلوبة
     */
    private function ensureConstantsExist(): void
    {
        $requiredTypes = [
            // 'marital_status' => ['أعزب', 'متزوج', 'مطلق', 'أرمل'],
            'money_status' => ['ميسور', 'متوسط', 'محتاج', 'فقير'],
            'name_prefix' => ['السيد', 'السيدة', 'الآنسة', 'الدكتور', 'المهندس'],
            'guardian_type' => ['أب', 'أم', 'جد', 'جدة', 'أخ', 'أخت', 'عم', 'خال'],
        ];

        foreach ($requiredTypes as $type => $values) {
            // if (Constant::where('constant_type_id', $type)->count() === 0) {
            if (Constant::whereHas('constantType', fn($q) => $q->where('name', $type))->count() === 0) {
                $this->command->info("جاري إنشاء ثوابت {$type}...");
                foreach ($values as $value) {
                    Constant::create([
                        'constant_type_id' => ConstantType::where('name', 'like', $type)->value('id'),
                        'name' => $value,
                        // 'order' => array_search($value, $values) + 1,
                    ]);
                }
            }
        }
    }

    /**
     * إنشاء طلاب لمسجد معين
     */
    private function createStudentsForSpecificMosque(): void
    {
        $mosque = Mosque::first();
        if ($mosque) {
            Student::factory(10)
                ->inMosque($mosque->id)
                ->create([
                    'mosque_id' => $mosque->id,
                ]);
        }
    }

    /**
     * إنشاء طلاب مع أولياء أمور محددين
     */
    private function createStudentsWithSpecificGuardians(): void
    {
        $guardians = User::take(5)->get();

        foreach ($guardians as $guardian) {
            // لكل ولي أمر، أنشئ 1-3 طلاب
            $count = rand(1, 3);
            Student::factory($count)
                ->withGuardian($guardian)
                ->create([
                    'guardian_id' => $guardian->identity,
                ]);
        }
    }

    /**
     * إنشاء طلاب ببيانات محددة
     */
    private function createSpecificStudents(): void
    {
        // الحصول على ولي أمر موجود أو إنشاء واحد جديد
        $guardian = User::first() ?? User::factory()->create();

        // طالب ذكر
        Student::factory()->male()->create([
            'fName' => 'محمد',
            'sName' => 'أحمد',
            'thName' => 'علي',
            'family' => 'السيد',
            'dob' => '2015-05-15',
            'guardian_id' => $guardian->identity,
            'phone' => '0501234567',
            'whatsapp' => '0501234567',
        ]);

        // طالب ذكر آخر
        Student::factory()->male()->create([
            'fName' => 'أحمد',
            'sName' => 'خالد',
            'thName' => null,
            'family' => 'العمر',
            'dob' => '2016-08-22',
            'guardian_id' => $guardian->identity,
        ]);

        // طالبة أنثى
        Student::factory()->female()->create([
            'fName' => 'فاطمة',
            'sName' => 'محمد',
            'thName' => 'عبدالله',
            'family' => 'الزهراني',
            'dob' => '2014-03-10',
            'guardian_id' => $guardian->identity,
            'phone' => '0509876543',
        ]);
    }
}