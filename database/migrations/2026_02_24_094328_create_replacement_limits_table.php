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
        Schema::create('replacement_limits', function (Blueprint $table) {
            $table->id();
            $table->integer('max_replacement_limit')->default(0);
            $table->text('min_replacement_limit')->default(0);
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date')->nullable();

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
        Schema::dropIfExists('replacement_limits');
    }
};
