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
        Schema::create('quran_pages', function (Blueprint $table) {
            $table->id();

            // بداية الصفحة
            $table->foreignId('from_surah_id')
                ->constrained('quran_surahs')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('from_ayah');

            // نهاية الصفحة
            $table->foreignId('to_surah_id')
                ->constrained('quran_surahs')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('to_ayah');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quran_pages');
    }
};
