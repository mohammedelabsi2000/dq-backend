<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->date('date');
            $table->unsignedTinyInteger('from_surah')->comment('رقم السورة من API السور');
            $table->foreign('from_surah')->references('id')->on('quran_surahs');
            $table->unsignedSmallInteger('from_ayah')->comment('رقم الآية البداية');
            $table->unsignedTinyInteger('to_surah')->comment('رقم السورة إلى API السور');
            $table->foreign('to_surah')->references('id')->on('quran_surahs');
            $table->unsignedSmallInteger('to_ayah')->comment('رقم الآية النهاية');
            // $table->integer('ayah_count')->comment('عدد الآيات المحفوظة');
            $table->string('achievement_type')->comment('نوع الحفظ: new_memorization, revision, recitation, exam');
            $table->string('evaluation_grade')->comment('درجة التقييم: excellent, very_good, good, acceptable, weak');
            $table->string('achievement_status')->comment('حالة الإنجاز: completed, partial, retry');
            $table->integer('mistakes_count')->default(0)->comment('عدد الأخطاء');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamp('recorded_at')->useCurrent()->comment('وقت التسجيل');
            $table->auditColumns();

            $table->index(['student_id', 'date']);
            $table->index(['teacher_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_achievements');
    }
};
