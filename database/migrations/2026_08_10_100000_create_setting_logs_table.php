<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('setting_logs', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->timestamp('start_dt');
            $table->timestamp('end_dt')->nullable();
            $table->auditColumns();

            $table->index(['key', 'end_dt']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('setting_logs');
    }
};
