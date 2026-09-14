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
        Schema::create('sponsor_branch_quotas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sponsor_id')
                ->constrained('sponsors')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->enum('gender', ['ذكر', 'أنثى'])->comment('الجنس الذي تخص به هذه الحصة');
            $table->unsignedInteger('quota')->comment('عدد الحلقات المحجوزة لهذا الفرع من هذا الجنس');
            $table->text('notes')->nullable();

            $table->auditColumns();

            $table->unique(['sponsor_id', 'branch_id', 'gender']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsor_branch_quotas');
    }
};
