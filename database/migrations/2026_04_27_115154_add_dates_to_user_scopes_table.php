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
        Schema::table('user_scopes', function (Blueprint $table) {
            // احذف الـ unique القديم
            $table->dropUnique(['user_id', 'scope_type', 'scope_id']);

            // أضف التواريخ
            $table->date('from_date')->nullable()->after('scope_id');
            $table->date('to_date')->nullable()->after('from_date');

            // unique جديد يشمل التاريخ
            $table->unique(['user_id', 'scope_type', 'scope_id', 'from_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_scopes', function (Blueprint $table) {
            $table->dropColumn(['from_date', 'to_date']);
        });
    }
};
