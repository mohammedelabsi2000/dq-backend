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
        // Student Previous Achievements Table
        // One to one relationship with students table
    
        Schema::create('previous_achievements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id')->unique()->comment('معرف الطالب');
            $table->foreign('student_id')->references('id')->on('students');

            $table->unsignedBigInteger('memorized_juz_id')->nullable()->comment('آخر اختبار');
            $table->foreign('memorized_juz_id')->references('id')->on('custom_juz');

            $table->unsignedBigInteger('completed_juz_id')->nullable()->comment('آخر سرد');
            $table->foreign('completed_juz_id')->references('id')->on('custom_juz');

            $table->unsignedTinyInteger('surah_id')->nullable()->comment('آخر سورة');
            $table->foreign('surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('end_aya')->nullable()->comment('رقم الآية');

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
        Schema::dropIfExists('student_previous_achievements');
    }
};
