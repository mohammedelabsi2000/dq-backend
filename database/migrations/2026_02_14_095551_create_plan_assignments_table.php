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
        Schema::create('plan_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            // manual / age / last_memorized / evaluation
            $table->string('assignment_type')
                ->comment('الفئة المستهدفة');

            // لتخزين المعايير على شكل JSON أو نص
            $table->string('criteria')->nullable()
                ->comment('معايير التنسيب على شكل JSON غالباً');

            // audit columns (macro)
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
        Schema::dropIfExists('plan_assignments');
    }
};
