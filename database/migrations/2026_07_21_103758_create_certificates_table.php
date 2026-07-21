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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->morphs('person');
            $table->string('certificate_link')->nullable()->comment('رابط الشهادة');
            $table->date('date_graduate')->nullable()->comment('تاريخ الحصول على الشهادة');
            $table->string('provider')->nullable()->comment('الجهة المشرفة');
            $table->enum('certificate_type', ['academy', 'course'])->comment('نوع الشهادة');
            $table->foreignId('academic_degree_id')->nullable()->constrained('constants');
            $table->foreignId('qualification_id')->nullable()->constrained('constants');
            $table->string('course_name')->nullable()->comment('اسم الدورة');
            $table->foreignId('course_type_id')->nullable()->constrained('constants');
            $table->text('notes')->nullable()->comment('ملاحظات');

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
        Schema::dropIfExists('certificates');
    }
};
