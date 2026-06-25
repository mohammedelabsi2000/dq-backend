<?php

use App\Enums\SuccessValueType;
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
        Schema::create('subject_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->enum('success_value_type', array_column(SuccessValueType::cases(), 'value'))->default('main_mark')->comment('نوع النجاح');
            $table->unsignedInteger('success_value')->default(0)->comment('قيمة النجاح من 0-100');
            $table->decimal('weight', 5, 2)->nullable()->comment('وزن / نسبة المتطلب داخل المادة');

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
        Schema::dropIfExists('subject_requirements');
    }
};
