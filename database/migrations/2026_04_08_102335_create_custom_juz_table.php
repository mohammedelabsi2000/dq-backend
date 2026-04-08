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
        Schema::create('custom_juz', function (Blueprint $table) {

            $table->id();
            
            $table->unsignedTinyInteger('sort_order');

            $table->string('name')->unique();

            $table->unsignedTinyInteger('start_surah_id')->comment('رقم السورة التي يبدأ بها الجزء');
            $table->foreign('start_surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('start_aya')->comment('رقم الآية التي يبدأ بها الجزء');

            $table->unsignedTinyInteger('end_surah_id')->comment('رقم السورة التي ينتهي بها الجزء');
            $table->foreign('end_surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('end_aya')->comment('رقم الآية التي ينتهي بها الجزء');

            $table->index(['start_surah_id', 'start_aya']);
            $table->index(['end_surah_id', 'end_aya']);

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
        Schema::dropIfExists('custom_juz');
    }
};
