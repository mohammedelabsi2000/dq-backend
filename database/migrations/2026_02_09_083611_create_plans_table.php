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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->foreignId('type_id')
                ->constrained('constants')
                ->cascadeOnDelete();

            $table->text('description')->nullable();

            $table->foreignId('target_group_id')
                ->constrained('constants')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('level_numbers')->nullable();

            $table->text('notes')->nullable();

            // audit columns (macro)
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
        Schema::dropIfExists('plans');
    }
};
