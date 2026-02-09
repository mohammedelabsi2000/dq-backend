<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('fName', 100)->nullable()->after('id');
            $table->string('sName', 100)->nullable()->after('fName');
            $table->string('thName', 100)->nullable()->after('sName');
            $table->string('family', 100)->nullable()->after('thName');

            $table->date('dob')->nullable()->after('family');

            $table->unsignedBigInteger('mosque_id')->nullable()->after('dob');
            $table->string('location', 255)->nullable()->after('mosque_id');

            $table->enum('gender', ['male', 'female'])->nullable()->after('location');

            $table->unsignedBigInteger('marital_status_id')->nullable()->after('gender');
            $table->integer('numChildren')->nullable()->after('marital_status_id');

            $table->string('identity', 50)->nullable()->after('password');
            $table->string('phone', 50)->nullable()->after('identity');
            $table->string('whatsapp', 50)->nullable()->after('phone');
            $table->string('jobname', 150)->nullable()->after('email');
            $table->string('job_place', 150)->nullable()->after('jobname');
            $table->decimal('job_salary', 10, 2)->nullable()->after('job_place');

            $table->string('image', 255)->nullable()->after('job_salary');

            $table->unsignedBigInteger('prefix_name_id')->nullable()->after('image');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
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
