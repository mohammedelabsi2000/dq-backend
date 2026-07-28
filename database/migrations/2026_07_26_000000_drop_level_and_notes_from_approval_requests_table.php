<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn(['current_level', 'starting_level', 'notes']);
        });
    }

    public function down()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->string('current_level')->nullable();
            $table->string('starting_level')->nullable();
            $table->text('notes')->nullable();
        });
    }
};
