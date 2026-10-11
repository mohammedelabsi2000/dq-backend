<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ربط السرد التجريبي والاختبار التجريبي بسجل الطالب في المادة (student_subjects)
     * حتى تكون اختبارات كل خطة مستقلة عن الأخرى، ورقم المحاولة فريداً داخل السجل.
     */
    public function up(): void
    {
        foreach (['recitation_exams', 'trial_exams'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('student_subject_id')
                    ->nullable()
                    ->after('subject_id')
                    ->comment('سجل الطالب في المادة - يحدد الخطة/المستوى الذي يتبعه الاختبار')
                    ->constrained('student_subjects');

                // فهرس مستقل للطالب قبل حذف الفهرس الفريد الذي يعتمد عليه المفتاح الأجنبي
                $table->index('student_id');
            });

            // ربط السجلات الحالية بآخر سجل للطالب في نفس المادة
            DB::table($tableName)->orderBy('id')->get(['id', 'student_id', 'subject_id'])->each(function ($exam) use ($tableName) {
                DB::table($tableName)->where('id', $exam->id)->update([
                    'student_subject_id' => DB::table('student_subjects')
                        ->where('student_id', $exam->student_id)
                        ->where('subject_id', $exam->subject_id)
                        ->whereNull('deleted_at')
                        ->max('id'),
                ]);
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['student_id', 'subject_id', 'attempt_number']);
                $table->unique(['student_subject_id', 'attempt_number']);
            });
        }

        // علامة النجاح تُنسخ من المادة، وقد لا تكون محددة لحظة فتح الاختبار
        DB::statement("ALTER TABLE trial_exams MODIFY success_mark DECIMAL(5,2) NULL COMMENT 'علامة النجاح - تُنسخ من المادة'");
    }

    public function down(): void
    {
        foreach (['recitation_exams', 'trial_exams'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['student_subject_id', 'attempt_number']);
                $table->unique(['student_id', 'subject_id', 'attempt_number']);
                $table->dropIndex(['student_id']);
                $table->dropConstrainedForeignId('student_subject_id');
            });
        }
    }
};
