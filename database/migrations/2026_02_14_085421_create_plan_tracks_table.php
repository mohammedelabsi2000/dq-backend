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
        Schema::create('plan_tracks', function (Blueprint $table) {
            $table->id(); // id - المعرف

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();
            // plan_id - معرف الخطة

            $table->foreignId('track_id')
                ->constrained()
                ->restrictOnDelete();
            // track_id - معرف المسار

            $table->boolean('is_required')->default(true)
                ->comment('هل المسار إجباري داخل هذه الخطة');

            $table->integer('weight')->default(1)
                ->comment('وزن المسار داخل الخطة');

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
        Schema::dropIfExists('plan_tracks');
    }
};
