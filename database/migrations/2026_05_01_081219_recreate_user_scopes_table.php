<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_scopes', function (Blueprint $table) {
            // 1. احذف الـ foreign key أولاً
            $table->dropForeign('user_scopes_user_id_foreign');

            // 2. احذف الـ index
            $table->dropIndex('user_scopes_user_id_scope_type_scope_id_from_date_unique');

            // 3. أضف role_id
            $table->unsignedBigInteger('role_id')->nullable()->after('user_id');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();

            // 4. أعد foreign key الـ user_id
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // 5. unique constraint جديد
            $table->unique(
                ['user_id', 'role_id', 'scope_type', 'scope_id', 'from_date'],
                'user_scopes_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('user_scopes', function (Blueprint $table) {
            $table->dropForeign('user_scopes_user_id_foreign');
            $table->dropUnique('user_scopes_unique');
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->unique(
                ['user_id', 'scope_type', 'scope_id', 'from_date'],
                'user_scopes_user_id_scope_type_scope_id_from_date_unique'
            );
        });
    }
};
