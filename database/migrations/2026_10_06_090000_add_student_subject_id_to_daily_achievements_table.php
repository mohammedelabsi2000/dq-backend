<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_achievements', function (Blueprint $table) {
            $table->foreignId('student_subject_id')
                ->nullable()
                ->after('subject_id')
                ->comment('سجل الطالب في المادة - يحدد الخطة/المستوى الذي سُجّل فيه الإنجاز')
                ->constrained('student_subjects')
                ->nullOnDelete();
        });

        // ربط الإنجازات الحالية بآخر سجل للطالب في نفس المادة
        DB::table('daily_achievements')
            ->whereNotNull('subject_id')
            ->orderBy('id')
            ->get(['id', 'student_id', 'subject_id'])
            ->each(function ($achievement) {
                $studentSubjectId = DB::table('student_subjects')
                    ->where('student_id', $achievement->student_id)
                    ->where('subject_id', $achievement->subject_id)
                    ->whereNull('deleted_at')
                    ->max('id');

                if ($studentSubjectId) {
                    DB::table('daily_achievements')
                        ->where('id', $achievement->id)
                        ->update(['student_subject_id' => $studentSubjectId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('daily_achievements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_subject_id');
        });
    }
};
