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
                ->virtualAs("CONCAT(fName, ' ', sName, ' ', thName, ' ', family)");

            $table->date('dob')->nullable();

            $table->foreignId('mosque_id')->constrained('mosques')->cascadeOnDelete();

            $table->text('location')->nullable();

            $table->enum('gender', ['male', 'female']);

            $table->foreignId('marital_status_id')->nullable()->constrained('constants');
            $table->foreignId('money_status_id')->nullable()->constrained('constants');
            $table->foreignId('prefix_name_id')->nullable()->constrained('constants');

            // رقم هوية ولي الأمر
            // $table->string('guardian_id'); // users.identity

            // $table->foreign('guardian_id')
            //     ->references('identity')
            //     ->on('users');
            // صلة قرابة ولي الأمر
            // $table->foreignId('guardian_type_id')->nullable()->constrained('constants');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();

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
