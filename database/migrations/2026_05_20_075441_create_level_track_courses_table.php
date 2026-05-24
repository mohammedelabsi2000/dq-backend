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
        Schema::create('level_track_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_track_id')->constrained('level_tracks')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->boolean('is_required')->default(true)->comment('إلزامي / اختياري');
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();

            $table->unique(['level_track_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_track_courses');
    }
};
