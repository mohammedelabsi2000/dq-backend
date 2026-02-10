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
        Schema::create('plan_levels', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // العلاقة مع plans
            $table->foreignId('plan_id')
                ->constrained('plans')
                ->cascadeOnDelete();

            // ترتيب المستوى داخل الخطة
            $table->unsignedTinyInteger('level_order');

            // زمن المستوى الأساسي
            $table->unsignedInteger('time_of_level')->nullable();
            $table->foreignId('time_unit_id')
                ->nullable()
                ->constrained('constants')
                ->nullOnDelete();

            // الحد الأعلى للزمن
            $table->unsignedInteger('max_time')->nullable();
            $table->foreignId('max_time_unit_id')
                ->nullable()
                ->constrained('constants')
                ->nullOnDelete();

            // الحد الأدنى للزمن
            $table->unsignedInteger('min_time')->nullable();
            $table->foreignId('min_time_unit_id')
                ->nullable()
                ->constrained('constants')
                ->nullOnDelete();

            // كل ترتيب مستوى يكون فريد داخل نفس الخطة
            $table->unique(['plan_id', 'level_order']);

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
        Schema::dropIfExists('plan_levels');
    }
};
