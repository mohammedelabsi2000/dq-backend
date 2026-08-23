<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم أو جهة الكفالة');
            $table->string('project_number')->nullable()->comment('رقم المشروع');
            $table->string('follow_up_entity')->comment('جهة المتابعة');
            $table->enum('sponsorship_type', ['دائمة', 'مؤقتة'])->comment('نوع الكفالة');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('required_halaqat_male')->default(0)->comment('إجمالي الحلقات المطلوبة (طلاب)');
            $table->unsignedInteger('required_halaqat_female')->default(0)->comment('إجمالي الحلقات المطلوبة (طالبات)');
            $table->unsignedInteger('students_per_halaqa_male')->nullable()->comment('عدد الطلاب المطلوبين بكل حلقة (طلاب)');
            $table->unsignedInteger('students_per_halaqa_female')->nullable()->comment('عدد الطلاب المطلوبين بكل حلقة (طالبات)');

            $table->foreignId('student_type_id')
                ->constrained('constants')
                ->cascadeOnDelete()
                ->comment('نوع الطلاب المطلوب');
            $table->string('student_type_other_note')->nullable()->comment('توضيح عند اختيار أخرى');

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->auditColumns();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsors');
    }
};
