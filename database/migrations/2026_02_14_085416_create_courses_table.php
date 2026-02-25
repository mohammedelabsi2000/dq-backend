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
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // id - المعرف

            $table->foreignId('track_id')
                ->constrained()
                ->restrictOnDelete();
            // track_id - معرف المسار

            $table->string('name');
            // name - اسم الدورة

            $table->string('hours')->nullable()
                ->comment('عدد ساعات الدورة');

            $table->string('book_name')->nullable()
                ->comment('اسم كتاب الدورة');

            $table->text('description')->nullable();
            // description - وصف الدورة

            $table->integer('min_score')->default(70)
                ->comment('درجة النجاح');

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
        Schema::dropIfExists('courses');
    }
};
