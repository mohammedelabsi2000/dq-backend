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
        Schema::create('plan_track_courses', function (Blueprint $table) {
            $table->id(); // id - المعرف

            $table->foreignId('plan_track_id')
                ->constrained()
                ->restrictOnDelete();
            // plan_track_id - معرف مسار الخطة

            $table->foreignId('course_id')
                ->constrained()
                ->restrictOnDelete();
            // course_id - معرف الدورة

            $table->integer('order')->default(1)
                ->comment('ترتيب الدورة');

            $table->boolean('is_required')->default(true)
                ->comment('هل الدورة إجبارية');

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
        Schema::dropIfExists('plan_track_courses');
    }
};
