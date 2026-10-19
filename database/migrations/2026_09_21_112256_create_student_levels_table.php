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
        Schema::create('student_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('level_id')->constrained();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->text('notes')->nullable();

            $table->auditColumns();

            $table->unique(['student_id', 'level_id', 'from_date'], 'student_level_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_levels');
    }
};
