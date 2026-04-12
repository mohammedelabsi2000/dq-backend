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
        Schema::table('students', function (Blueprint $table) {
            $table->string('memorized_juz')->nullable()->comment('الاختبارات');
            $table->string('completed_juz')->nullable()->comment('السرد');
            
            $table->unsignedTinyInteger('surah_id')->nullable()->comment('آخر سورة');
            $table->foreign('surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('end_aya')->nullable()->comment('رقم الآية');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('memorized_juz');
            $table->dropColumn('completed_juz');
            $table->dropColumn('surah_id');
            $table->dropColumn('end_aya');
        });
    }
};
