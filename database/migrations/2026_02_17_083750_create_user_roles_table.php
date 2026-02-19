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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();

            // المستخدم
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // الدور
            $table->foreignId('role_id')
                ->constrained('constants')
                ->cascadeOnDelete();

            // polymorphic relation (halaqa, branch, etc...)
            $table->morphs('relation');
            // ينشئ:
            // relation_id (unsignedBigInteger)
            // relation_type (string + index)

            // فترة الصلاحية
            $table->date('from_date');
            $table->date('to_date')->nullable();

            // فهارس مهمة للأداء
            $table->index(['user_id', 'relation_type', 'relation_id']);
            $table->index(['to_date']);

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
        Schema::dropIfExists('user_roles');
    }
};
