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
        Schema::create('halaqas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();

            // FK -> centers
            $table->foreignId('center_id')
                ->constrained('centers')
                ->cascadeOnDelete();

            // FK -> constants (غيّر 'constants' لاسم جدول الثوابت الحقيقي عندك)
            $table->foreignId('constant_id')
                ->constrained('constants')
                ->restrictOnDelete();
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
        Schema::dropIfExists('halaqas');
    }
};
