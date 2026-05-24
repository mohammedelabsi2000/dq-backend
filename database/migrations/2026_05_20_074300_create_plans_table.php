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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('duration_unit', ['يوم', 'اسبوع', 'شهر', 'سنة'])->default('شهر')->comment('وحدة المدة: يوم، أسبوع، شهر، سنة');
            $table->unsignedInteger('duration')->comment('المدة');
            $table->unsignedInteger('tolerance')->default(0)->comment('السماحية بالأيام');
            $table->boolean('is_active')->default(true)->comment('فعالة / غير فعالة');
            $table->timestamps();
            $table->softDeletes();
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
