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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('fName');
            $table->string('sName')->nullable();
            $table->string('thName')->nullable();
            $table->string('family');

            $table->string('full_name')
                ->virtualAs("CONCAT(fName, ' ', sName, ' ', thName, ' ', family)")
                ->comment('الاسم كامل (عمود ظاهري)');

            $table->date('dob')->nullable();

            $table->foreignId('mosque_id')->constrained('mosques');

            $table->text('location')->nullable();

            $table->enum('gender', ['ذكر', 'أنثى']);

            $table->foreignId('marital_status_id')->nullable()->comment('الحالة الاجتماعية')
                ->constrained('constants');
            $table->foreignId('money_status_id')->nullable()->comment('الحالة المادية')
                ->constrained('constants');
            $table->foreignId('prefix_name_id')->nullable()->comment('بادئة الاسم (م، د، إلخ)')
                ->constrained('constants');

            $table->string('guardian_id', 9)->comment('رقم هوية ولي الأمر'); // users.identity

            $table->foreign('guardian_id')
                ->references('identity')
                ->on('users');

            $table->foreignId('guardian_type_id')->nullable()->comment('صلة قرابة ولي الأمر')
                ->constrained('constants');
            $table->string('phone', 25)->nullable();
            $table->string('whatsapp', 25)->nullable();

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
        Schema::dropIfExists('students');
    }
};
