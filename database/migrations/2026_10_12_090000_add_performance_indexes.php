<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // قائمة طلبات الاعتماد تُفلتر بالحالة وتُرتَّب بتاريخ الإنشاء
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'approval_requests_status_created_at_index');
        });

        // أساس تحديد رؤية الطلاب: طلاب حلقات معيّنة (مع حالة الانتساب الحالية)
        Schema::table('halaqa_students', function (Blueprint $table) {
            $table->index(['halaqa_id', 'student_id', 'to_date'], 'halaqa_students_halaqa_student_to_date_index');
        });

        // فلتر الجنس (GenderVisibilityScope) يُضاف لكل استعلام على هذه الجداول
        Schema::table('students', function (Blueprint $table) {
            $table->index(['gender', 'is_approved'], 'students_gender_is_approved_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['gender', 'is_approved'], 'users_gender_is_approved_index');
        });

        Schema::table('halaqas', function (Blueprint $table) {
            $table->index('gender', 'halaqas_gender_index');
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->index('gender', 'centers_gender_index');
        });
    }

    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropIndex('approval_requests_status_created_at_index');
        });

        Schema::table('halaqa_students', function (Blueprint $table) {
            $table->dropIndex('halaqa_students_halaqa_student_to_date_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_gender_is_approved_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_gender_is_approved_index');
        });

        Schema::table('halaqas', function (Blueprint $table) {
            $table->dropIndex('halaqas_gender_index');
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->dropIndex('centers_gender_index');
        });
    }
};
