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
        Schema::table('halaqas', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->comment('فعالة / غير فعالة')->after('type_id');
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
            $table->dropColumn('is_active');
        });
    }
};
