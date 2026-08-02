<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $userId = DB::table('users')->where('email', 'admin@tahfeez.dq')->value('id');

            if (! $userId) {
                return;
            }

            DB::table('model_has_roles')
                ->where('model_type', \App\Models\User::class)
                ->where('model_id', $userId)
                ->delete();

            DB::table('model_has_permissions')
                ->where('model_type', \App\Models\User::class)
                ->where('model_id', $userId)
                ->delete();

            DB::table('personal_access_tokens')
                ->where('tokenable_type', \App\Models\User::class)
                ->where('tokenable_id', $userId)
                ->delete();

            DB::table('users')->where('id', $userId)->delete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
