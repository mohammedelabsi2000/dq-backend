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
        Schema::table('subjects', function (Blueprint $table) {
            $table->decimal('standard_pass_mark', 10, 2)->nullable()->comment('العلامة المخصصة للنجاح في المادة');
            $table->unsignedBigInteger('standard_subject_id')->nullable()->comment('رقم المادة في الشؤون الإدارية');
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
            $table->dropColumn('standard_pass_mark');
            $table->dropColumn('standard_subject_id');
        });
    }
};
