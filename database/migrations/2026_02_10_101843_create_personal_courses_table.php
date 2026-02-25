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
        Schema::create('personal_courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_name');
            $table->text('notes')->nullable();
            $table->integer('hours')->nullable();
            $table->string('provider')->nullable()
                ->comment('الجهة المشرفة');
            $table->string('place')->nullable();
            $table->string('certificate_link')->nullable();
            $table->foreignId('type_id')->constrained('constants');

            // Polymorphic relation
            $table->morphs('person');

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
        Schema::dropIfExists('personal_courses');
    }
};
