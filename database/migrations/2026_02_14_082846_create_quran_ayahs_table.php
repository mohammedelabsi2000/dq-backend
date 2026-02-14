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
        Schema::create('quran_ayahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surah_id')
                ->constrained('quran_surahs')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('ayah_number');
            $table->text('ayah_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quran_ayahs');
    }
};
