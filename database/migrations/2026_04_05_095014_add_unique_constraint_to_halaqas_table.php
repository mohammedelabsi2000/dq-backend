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
        Schema::table('halaqas', function (Blueprint $table) {
            $table->unique(['name', 'reference_type', 'reference_id'], 'halaqas_unique_name_reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('halaqas', function (Blueprint $table) {
            $table->dropUnique('halaqas_unique_name_reference');
        });
    }
};
