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
            $table->id(); // id - المعرف

            $table->string('name');
            // name - اسم الخطة

            // $table->integer('weight')->default(1)->comment('وزن الخطة');

            $table->integer('duration_in_days')
                ->comment('مدة الخطة بالأيام');

            $table->integer('grace_period_days')->default(0)
                ->comment('فترة السماحية بالأيام');

            $table->boolean('is_active')->default(true)
                ->comment('هل الخطة مفعلة');

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
