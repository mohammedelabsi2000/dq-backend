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
       Schema::create('plan_track_courses', function (Blueprint $table) {
    $table->id(); // id - المعرف

    $table->foreignId('plan_track_id')
          ->constrained()
          ->onDelete('cascade');
    // plan_track_id - معرف مسار الخطة

    $table->foreignId('course_id')
          ->constrained()
          ->onDelete('cascade');
    // course_id - معرف الدورة

    $table->integer('order')->default(1);
    // order - ترتيب الدورة

    $table->boolean('is_required')->default(true);
    // is_required - هل الدورة إجبارية

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
        Schema::dropIfExists('plan_track_courses');
    }
};
