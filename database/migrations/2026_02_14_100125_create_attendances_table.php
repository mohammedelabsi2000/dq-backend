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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->morphs('person'); // person_id & person_type
            $table->foreignId('halaqa_id')->constrained('halaqas')->restrictOnDelete();
            $table->date('date');

            $table->foreignId('status_id')
                ->comment('حالة الحضور')
                ->constrained('constants')->restrictOnDelete();

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
        Schema::dropIfExists('attendances');
    }
};
