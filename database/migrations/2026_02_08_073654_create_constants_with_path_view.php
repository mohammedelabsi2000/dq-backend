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
        DB::statement("
            CREATE OR REPLACE VIEW constants_with_path AS
            WITH RECURSIVE constants_tree AS (
                -- roots
                SELECT
                    c.id,
                    c.name,
                    c.constant_type_id,
                    c.parent_id,
                    c.is_active,
                    c.notes,
                    CAST(c.name AS CHAR(500)) AS path
                FROM constants c
                WHERE c.parent_id IS NULL

                UNION ALL

                -- children
                SELECT
                    c.id,
                    c.name,
                    c.constant_type_id,
                    c.parent_id,
                    c.is_active,
                    c.notes,
                    CONCAT(ct.path, ' / ', c.name) AS path
                FROM constants c
                INNER JOIN constants_tree ct
                    ON c.parent_id = ct.id
            )
            SELECT
                ct.*,
                ct_types.name AS constant_type_name,
                ct_types.description AS constant_type_description,
                ct_types.notes AS constant_type_notes
            FROM constants_tree ct
            INNER JOIN constant_types ct_types
                ON ct.constant_type_id = ct_types.id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('constants_with_path_view');
    }
};