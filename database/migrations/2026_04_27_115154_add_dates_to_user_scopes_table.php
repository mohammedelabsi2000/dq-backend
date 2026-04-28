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

            // 1. حذف الـ foreign key
            $table->dropForeign(['user_id']);

            // 2. حذف الـ unique القديم
            $table->dropUnique(['user_id', 'scope_type', 'scope_id']);

            // 3. إضافة الأعمدة (لا تنسى تفعلهم!)
            $table->date('from_date')->nullable()->after('scope_id');
            $table->date('to_date')->nullable()->after('from_date');

            // 4. إضافة unique جديد
            $table->unique(['user_id', 'scope_type', 'scope_id', 'from_date']);

            // 5. إعادة الـ foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
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

            $table->dropForeign(['user_id']);

            $table->dropUnique(['user_id', 'scope_type', 'scope_id', 'from_date']);

            $table->dropColumn(['from_date', 'to_date']);

            $table->unique(['user_id', 'scope_type', 'scope_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
