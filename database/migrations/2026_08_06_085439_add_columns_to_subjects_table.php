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
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('standard_department_id')->nullable()->comment('رقم الدائرة في الشؤون الإدارية');
            $table->string('standard_department_name')->nullable()->comment('اسم الدائرة في الشؤون الإدارية');
            $table->enum('gender', ['ذكر', 'أنثى'])->nullable()->comment('الجنس المخصص للمادة');
            $table->decimal('success_mark', 10, 2)->nullable()->comment('علامة النجاح للمادة');
            $table->unsignedInteger('alerts_count')->nullable()->comment('عدد التنبيهات المسموح بها');
            $table->unsignedInteger('errors_count')->nullable()->comment('عدد الأخطاء المسموح بها');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('standard_department_id');
            $table->dropColumn('standard_department_name');
            $table->dropColumn('gender');
            $table->dropColumn('success_mark');
            $table->dropColumn('alerts_count');
            $table->dropColumn('errors_count');
        });
    }
};
