<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();

            // المستوى الذي بدأ منه الطالب (يبقى ثابتاً للتاريخ)
            $table->foreignId('starting_level_id')->nullable()->constrained('levels')->nullOnDelete();

            // المستوى الحالي (يتحدث مع تقدم الطالب)
            $table->foreignId('current_level_id')->nullable()->constrained('levels')->nullOnDelete();

            $table->date('from_date')->comment('تاريخ الالتحاق بالخطة');
            $table->date('to_date')->nullable()->comment('تاريخ الانتهاء أو الانتقال - null يعني نشط حالياً');

            $table->boolean('is_main')->default(false)->comment('هل هذه الخطة الرئيسية للطالب');

            $table->string('status')->default('active')->comment('active, completed, transferred, dropped');

            $table->text('notes')->nullable();
            $table->auditColumns();

            $table->index(['student_id', 'to_date']);
            $table->index(['student_id', 'is_main']);
            $table->index('plan_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_plans');
    }
};