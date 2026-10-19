<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Constant;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('level_id')->nullable()->constrained('levels');
            $table->foreignId('result_status_id')->nullable()->constrained('constants')->default(
                Constant::where('const_key', 'in_progress')
                    ->whereHas('constantType', fn($q) => $q->where('name', 'result_status'))
                    ->value('id')
            );
            // $table->foreignId('result_status_id')->nullable()->constrained('constants');
            $table->decimal('grade', 5, 2)->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->date('grade_date')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('student_subjects');
    }
};
