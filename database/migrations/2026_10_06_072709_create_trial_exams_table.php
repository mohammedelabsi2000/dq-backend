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
        Schema::create('trial_exams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students');

            $table->foreignId('subject_id')
                ->constrained('subjects');

            $table->decimal('mark', 5, 2)->nullable()->comment('الدرجة التي حصل عليها الطالب في المادة');
            $table->decimal('success_mark', 5, 2)->comment('علامة النجاح للمادة');

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
        Schema::dropIfExists('trial_exams');
    }
};
