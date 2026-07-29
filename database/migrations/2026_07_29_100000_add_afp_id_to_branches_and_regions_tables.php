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
        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('standard_branch_id')->nullable()->unique()->after('id');
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->unsignedBigInteger('standard_region_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('standard_branch_id');
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('standard_region_id');
        });
    }
};
