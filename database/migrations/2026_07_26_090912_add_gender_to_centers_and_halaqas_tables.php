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
        Schema::table('centers', function (Blueprint $table) {
            $table->enum('gender', ['ذكر', 'أنثى'])->default('ذكر');
        });
        
        Schema::table('halaqas', function (Blueprint $table) {
            $table->enum('gender', ['ذكر', 'أنثى'])->default('ذكر');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn('gender');
        });

        Schema::table('halaqas', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
