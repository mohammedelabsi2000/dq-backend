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
            $table->timestamp('submitted_at')->nullable()->after('requested_by');
            $table->timestamp('decided_at')->nullable()->after('status');
        });

        // تعبئة البيانات الحالية: تاريخ الإرسال = created_at،
        // وتاريخ القرار = updated_at لكل طلب منتهٍ (معتمد/مرفوض)
        DB::table('approval_requests')->update([
            'submitted_at' => DB::raw('created_at'),
        ]);

        DB::table('approval_requests')
            ->whereIn('status', ['approved', 'rejected'])
            ->update(['decided_at' => DB::raw('updated_at')]);
    }

    public function down()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn(['submitted_at', 'decided_at']);
        });
    }
};
