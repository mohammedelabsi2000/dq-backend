<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            // أعد تسمية الدور الموجود بدل إنشاء دور جديد لتفادي فقدان ربطه بالمستخدمين
            DB::table('roles')
                ->where('name', 'super_admin')
                ->where('guard_name', 'sanctum')
                ->update(['name' => 'المسؤول التقني الأعلى']);

            $roleId = DB::table('roles')
                ->where('name', 'المسؤول التقني الأعلى')
                ->where('guard_name', 'sanctum')
                ->value('id');

            $adminRoleId = DB::table('roles')
                ->where('name', 'مدير الدائرة')
                ->where('guard_name', 'sanctum')
                ->value('id');

            $userId = DB::table('users')->where('email', 'admin@tahfiz.dq')->value('id');

            // ملاحظة: model_type مخزّن وفق morph map كـ alias 'user' وليس اسم الكلاس الكامل
            if ($userId && $roleId) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id'    => $roleId,
                    'model_type' => 'user',
                    'model_id'   => $userId,
                ], []);
            }

            // اترك للمستخدم دور المسؤول التقني الأعلى فقط، كما طُلب
            if ($userId && $adminRoleId) {
                DB::table('model_has_roles')
                    ->where('role_id', $adminRoleId)
                    ->where('model_type', 'user')
                    ->where('model_id', $userId)
                    ->delete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
