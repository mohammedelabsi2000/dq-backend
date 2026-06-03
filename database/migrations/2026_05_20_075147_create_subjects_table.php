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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained('tracks');
            $table->foreignId('subject_type_id')->constrained('constants');

            $table->string('title');
            $table->string('sub_title')->nullable();
            $table->string('juzs')->nullable();
            $table->string('surahs')->nullable();
            $table->string('verses')->nullable();
            $table->string('pages')->nullable();

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

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
        Schema::dropIfExists('subjects');
    }
};
