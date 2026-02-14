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
        Schema::create('quran_surahs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم السورة
            $table->unsignedSmallInteger('ayah_count'); // عدد الآيات
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surahs');
    }
};
