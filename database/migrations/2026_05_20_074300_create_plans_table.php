<?php

use App\Enums\PeriodUnit;
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
        // Drop existing tables if they exist to avoid conflicts
        Schema::dropIfExists('plan_track_courses');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('plan_tracks');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('plan_assignments');
        Schema::dropIfExists('plans');
        
        // Create the plans table
        Schema::create('plans', function (Blueprint $table) {
            $periodUnits = array_map(fn($unit) => $unit->value, PeriodUnit::cases());

            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('period_unit', $periodUnits)->default(PeriodUnit::Month->value)->comment('وحدة المدة: يوم، أسبوع، شهر، سنة');
            $table->unsignedInteger('period')->comment('مدة الخطة');
            $table->unsignedInteger('min_period')->nullable()->comment('أقل مدة');
            $table->unsignedInteger('max_period')->nullable()->comment('أكثر مدة');
            $table->unsignedInteger('tolerance')->default(0)->comment('السماحية بالأيام');
            $table->boolean('is_active')->default(true)->comment('فعالة / غير فعالة');
            
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
        Schema::dropIfExists('plans');
    }
};
