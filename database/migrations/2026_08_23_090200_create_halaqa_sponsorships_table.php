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
        Schema::create('halaqa_sponsorships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('halaqa_id')
                ->constrained('halaqas')
                ->cascadeOnDelete();

            $table->foreignId('sponsor_id')
                ->constrained('sponsors')
                ->cascadeOnDelete();

            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->string('stop_reason')->nullable();
            $table->text('notes')->nullable();

            $table->auditColumns();

            $table->index(['halaqa_id', 'to_date']);
            $table->index(['sponsor_id', 'to_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('halaqa_sponsorships');
    }
};
