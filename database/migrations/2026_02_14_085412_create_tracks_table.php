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
      Schema::create('tracks', function (Blueprint $table) {
    $table->id(); // id - المعرف

    $table->string('name');
    // name - اسم المسار (الحفظ - القيم...)

    $table->text('description')->nullable();
    // description - وصف المسار

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
        Schema::dropIfExists('tracks');
    }
};
