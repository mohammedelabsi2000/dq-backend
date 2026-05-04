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
    public function up(): void
    {
        Schema::table('user_scopes', function (Blueprint $table) {
            // 1. احذف الـ foreign keys أولاً
            $table->dropForeign('user_scopes_user_id_foreign');
            $table->dropForeign('user_scopes_role_id_foreign');

            // 2. احذف الـ unique
            $table->dropUnique('user_scopes_unique');

            // 3. أعد الـ foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('user_scopes', function (Blueprint $table) {
            $table->dropForeign('user_scopes_user_id_foreign');
            $table->dropForeign('user_scopes_role_id_foreign');

            $table->unique(
                ['user_id', 'role_id', 'scope_type', 'scope_id', 'from_date'],
                'user_scopes_unique'
            );

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();
        });
    }
};
