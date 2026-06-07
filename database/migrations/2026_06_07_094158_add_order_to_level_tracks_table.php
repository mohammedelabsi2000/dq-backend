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
        Schema::table('level_tracks', function (Blueprint $table) {
            $table->unsignedInteger('order')->after('weight')->comment('الترتيب داخل المستوى');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('level_tracks', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
