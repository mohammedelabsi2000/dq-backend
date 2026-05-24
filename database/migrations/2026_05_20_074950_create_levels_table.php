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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans');
            $table->string('name');
            $table->unsignedInteger('order')->comment('الترتيب داخل الخطة');

            $table->enum('period_unit', ['يوم', 'اسبوع', 'شهر', 'سنة'])->default('شهر')->comment('وحدة المدة: يوم، أسبوع، شهر، سنة');
            $table->unsignedInteger('period')->comment('مدة المستوى');
            $table->unsignedInteger('min_period')->nullable()->comment('أقل مدة');
            $table->unsignedInteger('max_period')->nullable()->comment('أكثر مدة');

            $table->text('notes')->nullable()->comment('الملاحظات');
            $table->auditColumns();

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
