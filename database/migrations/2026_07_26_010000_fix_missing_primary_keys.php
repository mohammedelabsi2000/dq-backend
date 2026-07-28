<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جداول كثيرة في قاعدة البيانات فقدت PRIMARY KEY/AUTO_INCREMENT على عمود id
     * (رغم أن الميجريشنز الأصلية صحيحة وتستخدم bigIncrements/id())، ما كان يمنع
     * أي إدراج جديد فيها (مثال: خطأ تسجيل الدخول على personal_access_tokens).
     */
    private array $singleIdTables = [
        'academic_qualifications', 'approval_requests', 'attendances', 'audits',
        'branches', 'centers', 'constant_types', 'constants', 'custom_juz',
        'daily_achievements', 'failed_jobs', 'grades', 'halaqa_statuses',
        'halaqa_students', 'halaqas', 'images', 'jobs', 'level_track_subjects',
        'level_tracks', 'levels', 'mosques', 'personal_access_tokens',
        'personal_courses', 'plans', 'previous_achievements', 'quran_juz',
        'quran_pages', 'quran_surahs', 'quran_verses', 'regions',
        'replacement_limits', 'student_level_histories', 'student_plans',
        'students', 'subject_requirements', 'subjects', 'tracks', 'user_roles',
        'user_scopes', 'users',
    ];

    public function up()
    {
        foreach ($this->singleIdTables as $table) {
            if (!Schema::hasTable($table) || $this->hasPrimaryKey($table)) {
                continue;
            }

            $maxId = DB::table($table)->max('id') ?? 0;
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = " . ($maxId + 1));
        }

        foreach ([
            'model_has_permissions' => 'ALTER TABLE `model_has_permissions` ADD PRIMARY KEY `model_has_permissions_permission_model_type_primary` (`permission_id`, `model_id`, `model_type`)',
            'model_has_roles' => 'ALTER TABLE `model_has_roles` ADD PRIMARY KEY `model_has_roles_role_model_type_primary` (`role_id`, `model_id`, `model_type`)',
            'role_has_permissions' => 'ALTER TABLE `role_has_permissions` ADD PRIMARY KEY `role_has_permissions_permission_id_role_id_primary` (`permission_id`, `role_id`)',
        ] as $table => $statement) {
            if (!Schema::hasTable($table) || $this->hasPrimaryKey($table)) {
                continue;
            }

            DB::statement($statement);
        }
    }

    private function hasPrimaryKey(string $table): bool
    {
        return (bool) DB::selectOne("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
    }

    public function down()
    {
        foreach ($this->singleIdTables as $table) {
            DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY, MODIFY `id` BIGINT UNSIGNED NOT NULL");
        }

        DB::statement('ALTER TABLE `model_has_permissions` DROP PRIMARY KEY');
        DB::statement('ALTER TABLE `model_has_roles` DROP PRIMARY KEY');
        DB::statement('ALTER TABLE `role_has_permissions` DROP PRIMARY KEY');
    }
};
