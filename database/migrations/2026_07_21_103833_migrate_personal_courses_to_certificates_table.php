<?php

use App\Models\Certificate;
use App\Models\PersonalCourse;
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
        if (!Schema::hasColumn('certificates', 'legacy_personal_course_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->unsignedBigInteger('legacy_personal_course_id');
            });
        }

        DB::table('certificates')->insertUsing(
            [
                'legacy_personal_course_id',
                'person_type',
                'person_id',
                'certificate_link',
                'provider',
                'certificate_type',
                'course_name',
                'course_type_id',
                'notes',
                'created_at',
                'updated_at',
                'deleted_at',
                'created_by',
                'updated_by',
                'deleted_by',
            ],
            DB::table('personal_courses')->selectRaw("
                    id,
                    person_type,
                    person_id,
                    certificate_link,
                    provider,
                    'course',
                    course_name,
                    type_id,
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
                    ON c.legacy_personal_course_id = i.imageable_id
                SET
                    i.imageable_type = ?,
                    i.imageable_id = c.id
                WHERE i.imageable_type = ?
            ", [
            Certificate::class,
            'PersonalCourse',
        ]);

        if (Schema::hasColumn('certificates', 'legacy_personal_course_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('legacy_personal_course_id');
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
