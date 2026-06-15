<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('constants', function (Blueprint $table) {

            $table->string('const_key', 150)
                ->nullable()
                ->after('name')
                ->comment('الاسم البرمجي للثابت باللغة الإنجليزية');

            $table->boolean('is_system')
                ->default(false)
                ->after('is_active')
                ->comment('ثابت نظام لا يمكن تعديله أو حذفه');

            $table->unique(['constant_type_id', 'const_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('constants', function (Blueprint $table) {

            $table->dropUnique(['constant_type_id', 'const_key']);
            $table->dropColumn(['const_key', 'is_system',]);
        });
    }
};
