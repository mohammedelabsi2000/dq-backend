<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    // use AuditColumns;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('constant_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم نوع الثابت
            $table->string('description')->nullable(); // وصف النوع
            $table->string('notes')->nullable(); // ملاحظات
            // $table->auditColumns();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('constant_types');
    }
};
