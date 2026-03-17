<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// This seeder is responsible for populating the roles and their associated abilities in the database.
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminId = 1;//DB::table('users')->where('email', 'admin@example.com')->value('id');

        $rolesData = [];

        foreach ([
            'مدير الدائرة',
            // 'مدير فرع',
            // 'مدير منطقة',
            // 'مدير مركز',
            // 'محفظ',
            // 'معلم',
            // 'إداري',
            // 'متطوع',
        ] as $role) {
            $rolesData[] = ['name' => $role];
        }

        DB::table('roles')->insert($rolesData);

        // Fetching the IDs of the roles we just inserted
        $adminRoleId = DB::table('roles')->where('name', 'مدير الدائرة')->value('id');
        // $branchManagerRoleId = DB::table('roles')->where('name', 'مدير فرع')->value('id');
        // $regionManagerRoleId = DB::table('roles')->where('name', 'مدير منطقة')->value('id');
        // $centerManagerRoleId = DB::table('roles')->where('name', 'مدير مركز')->value('id');
        // $teacherRoleId = DB::table('roles')->where('name', 'محفظ')->value('id');

        // Defining abilities for the "مدير عام" role
        $role_abilities = [];
        foreach ([
            'branches',
            'regions',
            'centers',
            'mosques',
            'halaqas',
            'users',
            'constants',
            'students',
            'halaqa_students',
            'roles',
        ] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $role_abilities[] = [
                    'role_id' => $adminRoleId,
                    'ability' => "{$module}.{$action}",
                ];
            }
        }
        DB::table('role_abilities')->insert($role_abilities);

        // Assigning the "مدير عام" role to the admin user
        DB::table('role_user')->insert([
            ['authorizable_type' => 'user', 'authorizable_id' => $adminId, 'role_id' => $adminRoleId],
        ]);

        // Optionally, you can assign the "محفظ" role to some users for testing purposes
        /* $users = DB::table('users')->where('id', '!=', $adminId)->take(20)->get();
        foreach ($users as $user) {
            DB::table('role_user')->insert([
                ['authorizable_type' => 'user', 'authorizable_id' => $user->id, 'role_id' => $teacherRoleId],
            ]);
        } */

    }
}
