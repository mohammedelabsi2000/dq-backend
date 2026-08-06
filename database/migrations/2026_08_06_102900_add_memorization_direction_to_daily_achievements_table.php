<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('daily_achievements', function (Blueprint $table) {
            $table->string('memorization_direction')->nullable()->after('to_ayah');
        });
    }

    public function down()
    {
        Schema::table('daily_achievements', function (Blueprint $table) {
            $table->dropColumn('memorization_direction');
        });
    }
};
