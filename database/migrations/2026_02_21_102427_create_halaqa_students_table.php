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
            $table->foreignId('halaqa_id')->constrained('halaqas');
            $table->foreignId('student_id')->constrained('students');
            $table->date('from_date');
            $table->date('to_date')->nullable(); // اذا ممكن يكون فارغ
            $table->foreignId('enrollment_status_id')->constrained('constants');

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
