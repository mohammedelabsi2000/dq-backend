<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_level_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_plan_id')->constrained('student_plans')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();

            $table->date('from_date')->comment('تاريخ بداية هذا المستوى للطالب');
            $table->date('to_date')->nullable()->comment('تاريخ الانتقال منه - null يعني المستوى الحالي');

            $table->text('notes')->nullable();
            $table->auditColumns();

            $table->index(['student_plan_id', 'to_date']);
            $table->index('level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_level_histories');
    }
};