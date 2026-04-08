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
        //-------------------------------------------
        // Surahs Table
        //-------------------------------------------
        Schema::create('quran_surahs', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary()->comment('رقم السورة (من 1 إلى 114)');

            $table->string('name_ar')->comment('اسم السورة بالعربية');
            $table->string('name_en')->comment('اسم السورة بالإنجليزية');
            $table->string('name_transliteration')->nullable()->comment('الاسم المنطوق (Transliteration)');

            $table->unsignedSmallInteger('revelation_place')->comment('مكان النزول (1 = مكية, 2 = مدنية)');
            $table->string('revelation_place_ar')->comment('مكان النزول بالعربية (مكية / مدنية)');
            $table->string('revelation_place_en')->comment('مكان النزول بالإنجليزية (makkah / madinah)');

            $table->unsignedSmallInteger('verses_count')->comment('عدد الآيات في السورة');

            $table->unsignedInteger('words_count')->default(0)->comment('عدد الكلمات في السورة');
            $table->unsignedInteger('letters_count')->default(0)->comment('عدد الحروف في السورة');
        });


        //-------------------------------------------
        // Pages Table
        //-------------------------------------------
        Schema::create('quran_pages', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary()->comment('رقم الصفحة (من 1 إلى 604)');

            $table->unsignedTinyInteger('start_surah_id')->comment('رقم السورة التي تبدأ بها الصفحة');
            $table->foreign('start_surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('start_aya')->comment('رقم الآية التي تبدأ بها الصفحة');

            $table->unsignedTinyInteger('end_surah_id')->comment('رقم السورة التي تنتهي بها الصفحة');
            $table->foreign('end_surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('end_aya')->comment('رقم الآية التي تنتهي بها الصفحة');

            $table->unsignedInteger('letters_count')->default(0)->comment('عدد الحروف في الصفحة');

            $table->index(['start_surah_id', 'start_aya']);
            $table->index(['end_surah_id', 'end_aya']);
        });


        //-------------------------------------------
        // Juz Table
        //-------------------------------------------
        Schema::create('quran_juz', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary()->comment('رقم الجزء (من 1 إلى 30)');

            $table->string('name')->nullable()->comment('اسم الجزء');

            $table->unsignedTinyInteger('start_surah_id')->comment('رقم السورة التي يبدأ بها الجزء');
            $table->foreign('start_surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('start_aya')->comment('رقم الآية التي يبدأ بها الجزء');

            $table->unsignedTinyInteger('end_surah_id')->comment('رقم السورة التي ينتهي بها الجزء');
            $table->foreign('end_surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('end_aya')->comment('رقم الآية التي ينتهي بها الجزء');

            $table->index(['start_surah_id', 'start_aya']);
            $table->index(['end_surah_id', 'end_aya']);
        });


        //-------------------------------------------
        // Verses Table
        //-------------------------------------------
        Schema::create('quran_verses', function (Blueprint $table) {
            $table->id()->comment('المعرف الفريد للآية');

            $table->unsignedTinyInteger('surah_id')->comment('رقم السورة');
            $table->foreign('surah_id')->references('id')->on('quran_surahs');

            $table->unsignedSmallInteger('number')->comment('رقم الآية داخل السورة');

            $table->text('text_ar')->comment('نص الآية بالعربية (الرسم العثماني)');
            $table->text('text_en')->nullable()->comment('ترجمة الآية إلى الإنجليزية');

            $table->unsignedTinyInteger('juz_id')->comment('رقم الجزء');
            $table->foreign('juz_id')->references('id')->on('quran_juz');

            $table->unsignedSmallInteger('page_id')->index()->comment('رقم الصفحة');
            $table->foreign('page_id')->references('id')->on('quran_pages');

            $table->boolean('sajda')->default(false)->comment('هل تحتوي الآية على سجدة');

            $table->unsignedInteger('words_count')->default(0)->comment('عدد الكلمات في الآية');
            $table->unsignedInteger('letters_count')->default(0)->comment('عدد الحروف في الآية');

            // النسب داخل الصفحة
            $table->decimal('percentage_in_page', 6, 3)->nullable()->comment('نسبة حجم الآية من الصفحة (%)');
            $table->unsignedInteger('cumulative_before')->default(0)->comment('عدد الحروف قبل هذه الآية في الصفحة');
            $table->unsignedInteger('cumulative_total')->default(0)->comment('عدد الحروف حتى نهاية هذه الآية في الصفحة');
            $table->decimal('start_percentage_in_page', 6, 3)->nullable()->comment('بداية الآية كنسبة مئوية من الصفحة');
            $table->decimal('end_percentage_in_page', 6, 3)->nullable()->comment('نهاية الآية كنسبة مئوية من الصفحة');

            // منع التكرار
            $table->unique(['surah_id', 'number']);

            // فهارس
            $table->index(['surah_id']);

            // $table->foreign('surah_number')->references('number')->on('surahs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //-------------------------------------------
        // Down Migration
        //-------------------------------------------
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('quran_verses');
        Schema::dropIfExists('quran_pages');
        Schema::dropIfExists('quran_juz');
        Schema::dropIfExists('quran_surahs');

        Schema::enableForeignKeyConstraints();
    }
};
