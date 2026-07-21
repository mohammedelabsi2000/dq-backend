<?php

use App\Models\AcademicQualification;
use App\Models\Certificate;
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
        // Add a temporary column to hold the legacy academic qualification ID
        Schema::table('certificates', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_academic_qualification_id');
        });

        //
        Schema::table('certificates', function (Blueprint $table) {
            DB::transaction(function () {

                DB::table('certificates')->insertUsing(
                    [
                        'legacy_academic_qualification_id',
                        'person_type',
                        'person_id',
                        'certificate_link',
                        'date_graduate',
                        'provider',
                        'certificate_type',
                        'academic_degree_id',
                        'qualification_id',
                        'notes',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'created_by',
                        'updated_by',
                        'deleted_by',
                    ],
                    DB::table('academic_qualifications')->selectRaw("
                    id,
                    person_type,
                    person_id,
                    certificate_link,
                    date_graduate,
                    educational_institution,
                    'academy',
                    academic_degree_id,
                    major_id,
                    notes,
                    created_at,
                    updated_at,
                    deleted_at,
                    created_by,
                    updated_by,
                    deleted_by
                ")
                );

                DB::statement("
                UPDATE images i
                INNER JOIN certificates c
                    ON c.legacy_academic_qualification_id = i.imageable_id
                SET
                    i.imageable_type = ?,
                    i.imageable_id = c.id
                WHERE i.imageable_type = ?
            ", [
                    Certificate::class,
                    AcademicQualification::class,
                ]);
            });
        });

        // Drop the legacy_academic_qualification_id column after migration
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('legacy_academic_qualification_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            //
        });
    }
};
