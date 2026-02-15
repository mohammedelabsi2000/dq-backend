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
        Schema::create('courses', function (Blueprint $table) {
    $table->id(); // id - المعرف

    $table->foreignId('track_id')
          ->constrained()
          ->onDelete('cascade');
    // track_id - معرف المسار

    $table->string('name');
    // name - اسم الدورة

    $table->string('hours');
    // hours - عدد ساعات الدورة الدورة

    $table->string('book_name')->nullable();
    // book_name - اسم كتاب الدورة

    $table->text('description')->nullable();
    // description - وصف الدورة

    $table->integer('max_score')->default(100);
    // max_score - الدرجة العظمى

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
        Schema::dropIfExists('courses');
    }
};
