<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('halaqa_statuses', function (Blueprint $table) {
            // Execute raw SQL to alter the column, avoiding the need for doctrine/dbal,
            // which is required by Laravel's change() method.
            // $table->date('from_date')->nullable()->change();
            DB::statement("
                ALTER TABLE halaqa_statuses
                MODIFY COLUMN from_date DATE NULL
            ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('halaqa_statuses', function (Blueprint $table) {
            //
        });
    }
};
