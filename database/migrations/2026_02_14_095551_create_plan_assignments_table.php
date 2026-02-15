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
       Schema::create('plan_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->string('assignment_type'); // manual / age / last_memorized / evaluation
    $table->string('criteria')->nullable(); // لتخزين المعايير على شكل JSON أو نص
    $table->timestamps();
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
