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
        Schema::create('halaqa_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('halaqa_id')->constrained('halaqas')->cascadeOnDelete(); // افتراض وجود جدول halaqas
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete(); // افتراض وجود جدول students
            $table->date('from_date');
            $table->date('to_date')->nullable(); // اذا ممكن يكون فارغ
            $table->foreignId('status_id')->constrained('constants'); // افتراض وجود جدول statuses

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
        Schema::dropIfExists('halaqa_students');
    }
};
