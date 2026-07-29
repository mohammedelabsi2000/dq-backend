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
        DB::transaction(function () {
            DB::statement("
                UPDATE halaqas h
                JOIN constant_types ct_old
                    ON ct_old.name = 'halaqa_type'
                JOIN constants c_old
                    ON c_old.constant_type_id = ct_old.id
                AND c_old.name = 'حفظ'
                JOIN constant_types ct_new
                    ON ct_new.name = 'halaqa_types'
                JOIN constants c_new
                    ON c_new.constant_type_id = ct_new.id
                AND c_new.name = 'حفظ'
                SET h.type_id = c_new.id
                WHERE h.type_id = c_old.id;
            ");
            
            DB::statement("
                DELETE c
                FROM constants c
                JOIN constant_types ct
                    ON ct.id = c.constant_type_id
                WHERE ct.name = 'halaqa_type';
            ");
            
            DB::statement("
                DELETE
                FROM constant_types
                WHERE name = 'halaqa_type';
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
        //
    }
};
