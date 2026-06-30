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
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('custom_juz_id')->nullable()->after('subject_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            // $table->dropColumn('custom_juz_id');
            $table->dropForeign('subjects_custom_juz_id_foreign');
    $table->dropColumn('custom_juz_id');
        });
    }
};
