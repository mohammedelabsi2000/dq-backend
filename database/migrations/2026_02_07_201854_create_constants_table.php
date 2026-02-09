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
        Schema::create('constants', function (Blueprint $table) {

            $table->id();

            $table->string('name', 150);

            $table->foreignId('constant_type_id')
                ->constrained('constant_types')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('constants')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            // audit columns (macro)
            // $table->auditColumns();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('constants');
    }
};
