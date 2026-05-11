<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('rejection_reason');
        });
    }

    public function down()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
