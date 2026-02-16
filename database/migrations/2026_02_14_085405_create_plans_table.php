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
        Schema::create('plans', function (Blueprint $table) {
    $table->id(); // id - المعرف

    $table->string('name');
    // name - اسم الخطة

    $table->integer('weight')->default(1);
    // weight - وزن الخطة

    $table->integer('duration_in_days');
    // duration_in_days - مدة الخطة بالأيام

    $table->integer('grace_period_days')->default(0);
    // grace_period_days - فترة السماحية بالأيام

    $table->boolean('is_active')->default(true);
    // is_active - هل الخطة مفعلة

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
        Schema::dropIfExists('plans');
    }
};
