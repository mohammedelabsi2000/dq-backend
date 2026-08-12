<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->timestamp('opened_at')->nullable()->after('value');
            $table->timestamp('closed_at')->nullable()->after('opened_at');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['opened_at', 'closed_at']);
        });
    }
};
