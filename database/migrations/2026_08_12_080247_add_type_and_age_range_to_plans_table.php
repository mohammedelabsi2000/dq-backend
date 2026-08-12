<?php

use App\Enums\PlanType;
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
        Schema::table('plans', function (Blueprint $table) {
            $types = array_map(fn($type) => $type->value, PlanType::cases());

            $table->enum('type', $types)->default(PlanType::Main->value)->comment('نوع الخطة: رئيسية أم فرعية');
            $table->unsignedInteger('age_from')->nullable()->comment('عمر الطالب من');
            $table->unsignedInteger('age_to')->nullable()->comment('عمر الطالب إلى');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['type', 'age_from', 'age_to']);
        });
    }
};
