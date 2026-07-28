<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // جدولي permissions وroles أُنشئا بعمود id بدون AUTO_INCREMENT أو PRIMARY KEY
        // بسبب حالة قديمة لقاعدة البيانات، ما يمنع إدراج صفوف جديدة (مثل صلاحيات الاعتماد الجديدة)
        // (تخطي التعديل لو الجدول أصلاً فيه PRIMARY KEY سليم، زي قاعدة بيانات جديدة)
        foreach (['permissions', 'roles'] as $table) {
            $hasPrimaryKey = DB::selectOne(
                "SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'"
            );

            if ($hasPrimaryKey) {
                continue;
            }

            $maxId = DB::table($table)->max('id') ?? 0;
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = " . ($maxId + 1));
        }
    }

    public function down()
    {
        foreach (['permissions', 'roles'] as $table) {
            DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY, MODIFY `id` BIGINT UNSIGNED NOT NULL");
        }
    }
};
