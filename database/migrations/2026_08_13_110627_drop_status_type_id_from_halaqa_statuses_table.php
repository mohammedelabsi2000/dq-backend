<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('halaqa_statuses', function (Blueprint $table) {
            $table->dropForeign(['status_type_id']);
            $table->dropColumn('status_type_id');
        });

        // إزالة ثوابت "فعالة/غير فعالة" التي حلّ محلها عمود halaqas.is_active
        $constantTypeId = DB::table('constant_types')->where('name', 'status_type')->value('id');

        if ($constantTypeId) {
            DB::table('constants')->where('constant_type_id', $constantTypeId)->delete();
            DB::table('constant_types')->where('id', $constantTypeId)->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('halaqa_statuses', function (Blueprint $table) {
            $table->foreignId('status_type_id')->nullable()->comment('نوع الحالة')
                ->after('halaqa_id')
                ->constrained('constants');
        });
    }
};
