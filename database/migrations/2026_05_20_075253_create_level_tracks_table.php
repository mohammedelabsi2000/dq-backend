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
        Schema::create('level_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->foreignId('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->default(0)->comment('وزن / نسبة المسار داخل المستوى');
            $table->auditColumns();

            $table->unique(['level_id', 'track_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_tracks');
    }
};
