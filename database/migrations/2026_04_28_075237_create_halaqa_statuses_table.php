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
        Schema::create('halaqa_statuses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('halaqa_id')->comment('الحلقة')
                ->constrained('halaqas');
            $table->foreignId('status_type_id')->nullable()->comment('نوع الحالة')
                ->constrained('constants');
            $table->foreignId('sponsorship_type_id')->nullable()->comment('نوع الكفالة')
                ->constrained('constants');
            $table->date('from_date');
            $table->date('to_date')->nullable();
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
        Schema::dropIfExists('halaqa_statuses');
    }
};
