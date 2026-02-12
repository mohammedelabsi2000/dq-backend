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
        Schema::create('academic_qualifications', function (Blueprint $table) {
            $table->id();

            // العلاقات مع constants
            $table->foreignId('academic_degree_id')
                ->constrained('constants')
                ->cascadeOnDelete();

            $table->foreignId('major_id')
                ->constrained('constants')
                ->cascadeOnDelete();

            // Morph relation
            $table->morphs('person'); // person_type + person_id

            $table->string('detail')->nullable();
            $table->date('date_graduate')->nullable();
            $table->string('certificate_link')->nullable();
            $table->string('educational_institution')->nullable();
            $table->text('notes')->nullable();

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
        Schema::dropIfExists('academic_qualifications');
    }
};
