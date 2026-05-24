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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->comment('الترتيب داخل الخطة');
            $table->enum('duration_unit', ['يوم', 'اسبوع', 'شهر', 'سنة'])->default('شهر')->comment('وحدة المدة: يوم، أسبوع، شهر، سنة');
            $table->unsignedInteger('duration')->comment('المدة الافتراضية');
            $table->unsignedInteger('max_duration')->comment('أقصى مدة');
            $table->unsignedInteger('min_duration')->comment('أدنى مدة');
            $table->text('notes')->nullable()->comment('الملاحظات');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['plan_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('levels');
    }
};
