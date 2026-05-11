<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('halaqas', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('type_id');
        });
    }

    public function down()
    {
        Schema::table('halaqas', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};
