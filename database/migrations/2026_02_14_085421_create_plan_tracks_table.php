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
        Schema::create('plan_tracks', function (Blueprint $table) {
    $table->id(); // id - المعرف

    $table->foreignId('plan_id')
          ->constrained()
          ->onDelete('cascade');
    // plan_id - معرف الخطة

    $table->foreignId('track_id')
          ->constrained()
          ->onDelete('cascade');
    // track_id - معرف المسار

    $table->boolean('is_required')->default(false);
    // is_required - هل المسار إجباري

    $table->integer('weight')->default(1);
    // weight - وزن المسار داخل الخطة

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
        Schema::dropIfExists('plan_tracks');
    }
};
