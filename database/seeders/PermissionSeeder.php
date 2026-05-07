<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // تنظيف الـ cache
        // app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();
        Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $permissions = [
            ['name' => 'branches.show', 'title' => 'عرض الفروع'],
            ['name' => 'branches.create', 'title' => 'إضافة فرع'],
            ['name' => 'branches.update', 'title' => 'تعديل فرع'],
            ['name' => 'branches.delete', 'title' => 'حذف فرع'],
            ['name' => 'regions.show', 'title' => 'عرض المناطق'],
            ['name' => 'regions.create', 'title' => 'إضافة منطقة'],
            ['name' => 'regions.update', 'title' => 'تعديل منطقة'],
            ['name' => 'regions.delete', 'title' => 'حذف منطقة'],
            ['name' => 'centers.show', 'title' => 'عرض المراكز'],
            ['name' => 'centers.create', 'title' => 'إضافة مركز'],
            ['name' => 'centers.update', 'title' => 'تعديل مركز'],
            ['name' => 'centers.delete', 'title' => 'حذف مركز'],
            ['name' => 'mosques.show', 'title' => 'عرض المساجد'],
            ['name' => 'mosques.create', 'title' => 'إضافة مسجد'],
            ['name' => 'mosques.update', 'title' => 'تعديل مسجد'],
            ['name' => 'mosques.delete', 'title' => 'حذف مسجد'],
            ['name' => 'halaqas.show', 'title' => 'عرض الحلقة'],
            ['name' => 'halaqas.create', 'title' => 'إضافة حلقة'],
            ['name' => 'halaqas.update', 'title' => 'تعديل حلقة'],
            ['name' => 'halaqas.delete', 'title' => 'حذف حلقة'],
            ['name' => 'users.show', 'title' => 'عرض المستخدمين'],
            ['name' => 'users.create', 'title' => 'إضافة مستخدم'],
            ['name' => 'users.update', 'title' => 'تعديل مستخدم'],
            ['name' => 'users.delete', 'title' => 'حذف مستخدم'],
            ['name' => 'approvals.show', 'title' => 'عرض الطلبات'],
            ['name' => 'approvals.approve', 'title' => 'الموافقة على الطلب'],
            ['name' => 'approvals.reject', 'title' => 'رفض الطلب'],
            ['name' => 'approvals.resubmit', 'title' => 'إعادة تقديم الطلب'],
            ['name' => 'users.roles.update', 'title' => 'تعديل صلاحيات المستخدم'],
            ['name' => 'users.certificates.show', 'title' => 'عرض شهادات المستخدمين'],
            ['name' => 'users.certificates.update', 'title' => 'تعديل شهادة المستخدم'],
            ['name' => 'constants.show', 'title' => 'عرض الثوابت'],
            ['name' => 'constants.create', 'title' => 'إضافة ثابت'],
            ['name' => 'constants.update', 'title' => 'تعديل ثابت'],
            ['name' => 'constants.delete', 'title' => 'حذف ثابت'],
            ['name' => 'custom_juzs.show', 'title' => 'عرض الأجزاء المخصصة'],
            ['name' => 'custom_juzs.create', 'title' => 'إضافة جزء مخصص'],
            ['name' => 'custom_juzs.update', 'title' => 'تعديل جزء مخصص'],
            ['name' => 'custom_juzs.delete', 'title' => 'حذف جزء مخصص'],
            ['name' => 'students.show', 'title' => 'عرض الطلاب'],
            ['name' => 'students.create', 'title' => 'إضافة طالب'],
            ['name' => 'students.update', 'title' => 'تعديل طالب'],
            ['name' => 'students.delete', 'title' => 'حذف طالب'],
            ['name' => 'students.certificates.show', 'title' => 'عرض شهادات الطلاب'],
            ['name' => 'students.certificates.update', 'title' => 'تعديل شهادة الطالب'],
            ['name' => 'halaqa_students.show', 'title' => 'عرض طلاب الحلقة'],
            ['name' => 'halaqa_students.create', 'title' => 'إضافة طلاب الحلقة'],
            ['name' => 'halaqa_students.update', 'title' => 'تعديل طلاب الحلقة'],
            ['name' => 'halaqa_students.delete', 'title' => 'حذف طلاب الحلقة'],
            ['name' => 'roles.show', 'title' => 'عرض الصلاحيات'],
            ['name' => 'roles.create', 'title' => 'إضافة صلاحية'],
            ['name' => 'roles.update', 'title' => 'تعديل صلاحية'],
            ['name' => 'roles.delete', 'title' => 'حذف صلاحية'],
        ];

        // creating all permissions for both sanctum and web guards
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'title' => $permission['title'],
                'guard_name' => 'sanctum',
            ]);
        }

        // Create admin role with all permissions for both guards
        $adminRole = Role::firstOrCreate([
            'name' => 'مدير الدائرة',
            'guard_name' => 'sanctum',
        ]);

        $adminRole->syncPermissions(Permission::all());

        // Assign admin role to first user
        $firstUser = User::where('email', 'admin@tahfeez.dq')->first();
        if ($firstUser) {
            $firstUser->assignRole($adminRole);
        }
    }
}
