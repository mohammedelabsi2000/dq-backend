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
        Schema::create('level_track_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_track_id')->constrained('level_tracks');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->boolean('is_required')->default(true)->comment('إلزامي / اختياري');
            $table->unsignedInteger('order')->nullable();
            $table->decimal('weight', 5, 2)->default(0)->comment('وزن / نسبة المادة داخل المسار');
            $table->auditColumns();

            $table->unique(['level_track_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_track_subjects');
    }
};
