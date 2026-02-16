<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('fName', 100)->nullable()->after('id');
            $table->string('sName', 100)->nullable()->after('fName');
            $table->string('thName', 100)->nullable()->after('sName');
            $table->string('family', 100)->nullable()->after('thName');

            $table->string('full_name')
                ->virtualAs("CONCAT(fName, ' ', sName, ' ', thName, ' ', family)")->after('family');

            $table->date('dob')->nullable()->after('full_name');

            // Mosque FK
            $table->unsignedBigInteger('mosque_id')->nullable()->after('dob');
            $table->foreign('mosque_id')
                ->references('id')
                ->on('mosques')
                ->nullOnDelete();

            $table->string('location')->nullable()->after('mosque_id');

            $table->enum('gender', ['male', 'female'])->nullable()->after('location');

            // Marital Status FK (constants)
            $table->unsignedBigInteger('marital_status_id')->nullable()->after('gender');
            $table->foreign('marital_status_id')
                ->references('id')
                ->on('constants')
                ->nullOnDelete();

            $table->integer('numChildren')->nullable()->after('marital_status_id');

            $table->string('identity', 50)->nullable()->unique()->after('password');
            $table->string('phone', 50)->nullable()->after('identity');
            $table->string('whatsapp', 50)->nullable()->after('phone');

            $table->string('jobname')->nullable()->after('email');
            $table->string('job_place')->nullable()->after('jobname');
            $table->decimal('job_salary', 10, 2)->nullable()->after('job_place');


            // Image FK
            // $table->unsignedBigInteger('image_id')->nullable()->after('job_salary');

            // $table->foreignId('image_id')
            //       ->nullable()
            //       ->constrained('images')
            //       ->nullOnDelete()
            //       ->after('job_salary');

            // Prefix FK (constants)
            $table->unsignedBigInteger('prefix_name_id')->nullable();
            $table->foreign('prefix_name_id')
                ->references('id')
                ->on('constants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['mosque_id']);
            $table->dropForeign(['marital_status_id']);
            $table->dropForeign(['image']);
            $table->dropForeign(['prefix_name_id']);

            $table->dropColumn([
                'fName',
                'sName',
                'thName',
                'family',
                'dob',
                'mosque_id',
                'location',
                'gender',
                'marital_status_id',
                'numChildren',
                'identity',
                'phone',
                'whatsapp',
                'jobname',
                'job_place',
                'job_salary',
                'image',
                'prefix_name_id',
            ]);
        });
    }
};
