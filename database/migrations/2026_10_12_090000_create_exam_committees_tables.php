<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // لجان الاختبارات النهائية
        Schema::create('exam_committees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم اللجنة');
            $table->boolean('is_multi_subject')->default(false)->comment('هل اللجنة لعدة مساقات؟');
            $table->unsignedBigInteger('standard_committee_id')->nullable()->unique();
            $table->foreignId('subject_id')->nullable()->comment('المساق - للجنة المساق الواحد فقط')
                ->constrained('subjects');
            $table->date('start_date')->comment('تاريخ البدء');
            $table->enum('gender', ['ذكر', 'أنثى'])->comment('الفئة: طلاب أم طالبات');
            $table->foreignId('branch_id')->nullable()->comment('الفرع')->constrained('branches');
            $table->foreignId('region_id')->nullable()->comment('المحلية')->constrained('regions');
            $table->dateTime('proposed_exam_date')->nullable()->comment('موعد الاختبار المقترح');
            $table->string('exam_location')->nullable()->comment('مكان عقد الاختبار');
            $table->text('notes')->nullable();
            $table->auditColumns();
        });

        // الطلاب المنسَّبون للجنة (كل سجل هو طالب في مادة ضمن خطة محددة)
        Schema::create('exam_committee_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_committee_id')->constrained('exam_committees');
            $table->foreignId('student_subject_id')->comment('سجل الطالب في المادة')->constrained('student_subjects');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->auditColumns();

            $table->index(['student_subject_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_committee_students');
        Schema::dropIfExists('exam_committees');
    }
};
