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

        if (!Schema::hasColumn('certificates', 'legacy_academic_qualification_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->unsignedBigInteger('legacy_academic_qualification_id');
            });
        }

        DB::table('certificates')->insertUsing(
            [
                'legacy_academic_qualification_id',
                'person_type',
                'person_id',
                'certificate_link',
                'date_graduate',
                'provider',
                'certificate_type',
                'academic_qualification_id',
                'major_id',
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
                    CASE
                        WHEN date_graduate IS NULL THEN NULL
                        ELSE STR_TO_DATE(CONCAT(date_graduate, '-01-01'), '%Y-%m-%d')
                    END,
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
            'AcademicQualification',
        ]);

        if (Schema::hasColumn('certificates', 'legacy_academic_qualification_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('legacy_academic_qualification_id');
            });
        }
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
