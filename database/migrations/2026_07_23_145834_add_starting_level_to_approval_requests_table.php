<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->string('starting_level')->nullable()->after('current_level');
        });

        // نحاول استرجاع أول مستوى من approval_logs قبل حذفها للحفاظ على السجلات القديمة
        if (Schema::hasTable('approval_logs')) {
            $firstLevels = DB::table('approval_logs')
                ->select('approval_request_id', 'level')
                ->orderBy('created_at')
                ->get()
                ->unique('approval_request_id');

            foreach ($firstLevels as $row) {
                DB::table('approval_requests')
                    ->where('id', $row->approval_request_id)
                    ->update(['starting_level' => $row->level]);
            }
        }

        DB::table('approval_requests')
            ->whereNull('starting_level')
            ->update(['starting_level' => DB::raw('current_level')]);
    }

    public function down()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn('starting_level');
        });
    }
};
