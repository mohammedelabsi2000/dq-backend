<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // بيانات الإرسال للجودة على اللجنة
        Schema::table('exam_committees', function (Blueprint $table) {
            // قد يكون موجوداً في قواعد أُنشئ فيها الجدول بعد إضافته لملف الإنشاء
            if (!Schema::hasColumn('exam_committees', 'standard_committee_id')) {
                $table->unsignedBigInteger('standard_committee_id')->nullable()->unique()->after('id')
                    ->comment('رقم اللجنة في برنامج الجودة');
            }
            $table->dateTime('sent_at')->nullable()->after('status')->comment('تاريخ إرسال اللجنة للجودة');
            $table->dateTime('status_updated_at')->nullable()->after('sent_at')->comment('آخر تحديث للحالة من الجودة');
            $table->text('quality_notes')->nullable()->after('status_updated_at')->comment('ملاحظات الجودة (سبب الرفض مثلاً)');
        });

        // محاولات الاختبار النهائي: كل محاولة في لجنة، ونتيجتها تصل من الجودة
        Schema::create('final_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_subject_id')->comment('سجل الطالب في المادة')->constrained('student_subjects');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('exam_committee_id')->comment('اللجنة التي اختُبر فيها')->constrained('exam_committees');
            $table->unsignedInteger('attempt_number')->default(1);
            $table->decimal('mark', 5, 2)->nullable();
            $table->decimal('success_mark', 5, 2)->nullable()->comment('علامة النجاح منسوخة من المادة');
            $table->string('result')->comment('ناجح / راسب / غائب');
            $table->date('exam_date')->nullable();
            $table->text('notes')->nullable();
            $table->auditColumns();

            $table->index(['student_subject_id', 'exam_committee_id']);
        });

        // سجل كل ما يُرسل للجودة ويصل منها
        Schema::create('quality_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('direction')->comment('outgoing: إرسال للجودة، incoming: تحديث من الجودة');
            $table->foreignId('exam_committee_id')->nullable()->constrained('exam_committees');
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_sync_logs');
        Schema::dropIfExists('final_exams');

        Schema::table('exam_committees', function (Blueprint $table) {
            $table->dropColumn(['sent_at', 'status_updated_at', 'quality_notes']);
        });
    }
};
