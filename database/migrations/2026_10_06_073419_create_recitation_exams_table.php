<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recitation_exams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students');

            $table->foreignId('subject_id')
                ->constrained('subjects');

            $table->unsignedInteger('errors_count')->nullable()->comment('عدد أخطاء الطالب في السرد');
            $table->unsignedInteger('alerts_count')->nullable()->comment('عدد تنبيهات الطالب في السرد');

            $table->unsignedInteger('errors_count_allowed')->nullable()->comment('عدد الأخطاء المسموح بها');
            $table->unsignedInteger('alerts_count_allowed')->nullable()->comment('عدد التنبيهات المسموح بها');
            
            $table->unsignedInteger('attempt_number')->comment('رقم المحاولة');

            $table->date('exam_date')->nullable()->comment('تاريخ الاختبار');

            $table->text('notes')->nullable()->comment('ملاحظات حول الاختبار');

            $table->auditColumns();

            $table->unique([
                'student_id',
                'subject_id',
                'attempt_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recitation_exams');
    }
};
